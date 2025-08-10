<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;
use App\Http\Middleware\RateLimitMiddleware;
use App\Services\NotificationService;

class AuthController extends Controller
{
    /**
     * Multi-step registration process.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:seller,investor',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            
            // Professional details based on role
            'businessName' => 'required_if:role,seller|max:255',
            'businessType' => 'required_if:role,seller|max:255',
            'investmentAmount' => 'required_if:role,investor|max:255',
            'investmentFocus' => 'required_if:role,investor|max:255',
            
            // Compliance
            'agreeToTerms' => 'required|accepted',
            'agreeToPrivacy' => 'required|accepted',
            'confirmAge' => 'required|accepted',
            'confirmIdentity' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
        $user = User::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'name' => $request->firstName . ' ' . $request->lastName,
            'email' => $request->email,
                'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
                'business_name' => $request->businessName,
                'business_type' => $request->businessType,
                'investment_amount' => (string) $request->investmentAmount,
                'investment_focus' => (string) $request->investmentFocus,
            ]);

            // Log registration activity
            NotificationService::logRegistration($user);

            // Generate email verification token
            $emailToken = $user->generateEmailVerificationToken();
            
            // Send verification email
            $this->sendEmailVerification($user, $emailToken);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please check your email for verification.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'verification_step' => 'email'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'role' => $request->role,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Send email verification.
     */
    public function sendEmailVerification(User $user, string $token): void
    {
        try {
            $verificationUrl = config('app.frontend_url', 'http://localhost:3000') 
                . '/auth/verify-email?token=' . $token . '&email=' . urlencode($user->email);

            $data = [
                'user' => $user,
                'verification_url' => $verificationUrl,
                'token' => $token
            ];

            // Send actual email verification
            Mail::send('emails.email-verification', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Verify Your Email - BizInvestify');
            });

            Log::info('Email verification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'url' => $verificationUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email verification: ' . $e->getMessage());
        }
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }



        if ($user->verifyEmail($request->token)) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully!',
                'data' => [
                    'user_id' => $user->id,
                    'next_step' => 'phone_verification'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification token'
        ], 400);
    }

    /**
     * Check email verification status
     */
    public function checkEmailVerificationStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email_verified' => !is_null($user->email_verified_at),
                'user_id' => $user->id,
                'next_step' => !is_null($user->email_verified_at) ? 'phone_verification' : 'email_verification'
            ]
        ]);
    }

    /**
     * Resend email verification.
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 400);
        }

        $emailToken = $user->generateEmailVerificationToken();
        $this->sendEmailVerification($user, $emailToken);

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully!'
        ]);
    }

    /**
     * Send email verification OTP.
     */
    public function sendEmailVerificationOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email',
                'errors' => $validator->fails()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 400);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in user record
        $user->update([
            'email_verification_otp' => $otp,
            'email_verification_otp_expires_at' => now()->addMinutes(10)
        ]);

        // Send OTP via email
        $this->sendEmailVerificationOTPEmail($user, $otp);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your email!'
        ]);
    }

    /**
     * Verify email with OTP.
     */
    public function verifyEmailOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 400);
        }

        // Check if OTP is valid and not expired
        if ($user->email_verification_otp !== $request->otp ||
            !$user->email_verification_otp_expires_at ||
            $user->email_verification_otp_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code'
            ], 400);
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
            'email_verification_otp' => null,
            'email_verification_otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'data' => [
                'user_id' => $user->id,
                'next_step' => 'phone_verification'
            ]
        ]);
    }

    /**
     * Send email verification OTP email.
     */
    private function sendEmailVerificationOTPEmail(User $user, string $otp): void
    {
        try {
            $data = [
                'user' => $user,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10)->format('H:i')
            ];

            Mail::send('emails.email-verification-otp', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Email Verification Code - BizInvestify');
            });

            Log::info('Email verification OTP sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => $otp
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email verification OTP: ' . $e->getMessage());
        }
    }

    /**
     * Send phone verification code.
     */
    public function sendPhoneVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first'
            ], 400);
        }

        // Update phone number if provided
        if ($request->phone !== $user->phone) {
            $user->update(['phone' => $request->phone]);
        }

        $phoneToken = $user->generatePhoneVerificationToken();
        
        // In a real application, you would send SMS via Twilio, AWS SNS, etc.
        // For now, we'll just log it
        Log::info('Phone verification code sent', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'code' => $phoneToken
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your phone!',
            'data' => [
                'phone' => $user->phone,
                'expires_in' => 600 // 10 minutes
            ]
        ]);
    }

    /**
     * Verify phone with OTP code.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->verifyPhone($request->code)) {
            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully!',
                'data' => [
                    'user_id' => $user->id,
                    'next_step' => 'kyc_verification'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code'
        ], 400);
    }

    /**
     * Upload KYC documents.
     */
    public function uploadKyc(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|min:1',
            'documents.*.type' => 'required|string|in:passport,driverLicense,nationalId,proofOfAddress',
            'documents.*.file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->isPhoneVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete phone verification first'
            ], 400);
        }

        try {
            $documents = [];
            foreach ($request->documents as $document) {
                $file = $document['file'];
                $type = $document['type'];
                
                $path = $file->store('kyc-documents/' . $user->id, 'public');
                
                $documents[] = [
                    'type' => $type,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString(),
                ];
            }

            $user->update([
                'kyc_documents' => $documents,
                'kyc_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KYC documents uploaded successfully. Review will take 24-48 hours.',
                'data' => [
                    'kyc_status' => $user->kyc_status,
                    'documents_count' => count($documents),
                    'next_step' => 'setup_2fa'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('KYC upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Setup two-factor authentication.
     */
    public function setup2FA(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isPhoneVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete phone verification first'
            ], 400);
        }

        try {
            $google2fa = new Google2FA();
            $secretKey = $google2fa->generateSecretKey();

            // Store the secret temporarily (not confirmed yet)
            $user->update(['two_factor_secret' => $secretKey]);

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                'BizInvestify',
                $user->email,
                $secretKey
            );

            // Generate backup codes
            $backupCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $backupCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
            }

            $user->update(['two_factor_recovery_codes' => json_encode($backupCodes)]);

            return response()->json([
                'success' => true,
                'message' => '2FA setup initiated',
                'data' => [
                    'secret_key' => $secretKey,
                    'qr_code_url' => $qrCodeUrl,
                    'backup_codes' => $backupCodes,
                    'manual_entry_key' => $secretKey
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('2FA setup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '2FA setup failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Setup email-based 2FA.
     */
    public function setupEmail2FA(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isPhoneVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete phone verification first'
            ], 400);
        }

        try {
            // Generate a verification code for email 2FA
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store the code temporarily (not confirmed yet)
            $user->update([
                'email_2fa_code' => $verificationCode,
                'email_2fa_expires_at' => now()->addMinutes(10)
            ]);

            // Send verification email
            $data = [
                'user' => $user,
                'verification_code' => $verificationCode,
                'expires_at' => now()->addMinutes(10)->format('H:i')
            ];

            Mail::send('emails.email-2fa-setup', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Setup Email 2FA - BizInvestify');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email 2FA setup initiated. Check your email for the verification code.',
                'data' => [
                    'email' => $user->email,
                    'expires_at' => now()->addMinutes(10)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Email 2FA setup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Email 2FA setup failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Skip 2FA setup (make it optional).
     */
    public function skip2FA(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isPhoneVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete phone verification first'
            ], 400);
        }

        try {
            // Mark 2FA as skipped (optional)
            $user->update([
                'two_factor_skipped' => true,
                'two_factor_confirmed_at' => now() // Mark as complete but skipped
            ]);

            return response()->json([
                'success' => true,
                'message' => '2FA setup skipped successfully!',
                'data' => [
                    'user_id' => $user->id,
                    'verification_complete' => $user->isFullyVerified(),
                    'next_step' => 'complete'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('2FA skip failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to skip 2FA. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify and confirm 2FA setup.
     */
    public function confirm2FA(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'success' => false,
                'message' => 'Please setup 2FA first'
            ], 400);
        }

        try {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if ($valid) {
                $user->update(['two_factor_confirmed_at' => now()]);

                return response()->json([
                    'success' => true,
                    'message' => '2FA setup completed successfully!',
                    'data' => [
                        'user_id' => $user->id,
                        'verification_complete' => $user->isFullyVerified(),
                        'next_step' => 'complete'
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);

        } catch (\Exception $e) {
            Log::error('2FA confirmation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify email-based 2FA setup.
     */
    public function confirmEmail2FA(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->email_2fa_code) {
            return response()->json([
                'success' => false,
                'message' => 'Please setup email 2FA first'
            ], 400);
        }

        try {
            // Check if code matches and is not expired
            if ($user->email_2fa_code === $request->code && 
                $user->email_2fa_expires_at && 
                $user->email_2fa_expires_at->isFuture()) {
                
                $user->update([
                    'email_2fa_enabled' => true,
                    'email_2fa_code' => null,
                    'email_2fa_expires_at' => null,
                    'two_factor_confirmed_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email 2FA setup completed successfully!',
                    'data' => [
                        'user_id' => $user->id,
                        'verification_complete' => $user->isFullyVerified(),
                        'next_step' => 'complete'
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Email 2FA confirmation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Login user with optional 2FA.
     */
    public function login(Request $request): JsonResponse
    {
        // Check if user is locked out due to too many failed attempts
        if (RateLimitMiddleware::checkAuthAttempts($request, 5, 15)) {
            $retryAfter = RateLimitMiddleware::getAuthAttemptsAvailableIn($request);
            
            Log::warning('Login attempt blocked due to rate limiting', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'user_agent' => substr($request->userAgent(), 0, 200),
                'retry_after' => $retryAfter
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please try again later.',
                'retry_after' => $retryAfter,
                'remaining_attempts' => 0
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:255',
            'two_factor_code' => 'nullable|string|size:6',
            'remember_device' => 'boolean'
        ]);

        if ($validator->fails()) {
            Log::info('Login validation failed', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Record failed attempt
            RateLimitMiddleware::recordFailedAuthAttempt($request, 15);
            
            // Log failed login attempt with notification service
            NotificationService::logFailedLogin($request->input('email'));
            
            $remainingAttempts = RateLimitMiddleware::getRemainingAuthAttempts($request, 5);
            
            Log::warning('Login failed - invalid credentials', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'user_agent' => substr($request->userAgent(), 0, 200),
                'remaining_attempts' => $remainingAttempts
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'remaining_attempts' => $remainingAttempts
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            if (!$request->two_factor_code) {
                Log::info('2FA code required for login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '2FA code required',
                    'data' => [
                        'requires_2fa' => true,
                        'user_id' => $user->id
                    ]
                ], 200);
            }

            // Verify 2FA code
            try {
                $google2fa = new Google2FA();
                $valid = $google2fa->verifyKey($user->two_factor_secret, $request->two_factor_code);

                if (!$valid) {
                    // Record failed 2FA attempt
                    RateLimitMiddleware::recordFailedAuthAttempt($request, 15);
                    
                    $remainingAttempts = RateLimitMiddleware::getRemainingAuthAttempts($request, 5);
                    
                    Log::warning('Invalid 2FA code provided', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                        'remaining_attempts' => $remainingAttempts
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid 2FA code',
                        'remaining_attempts' => $remainingAttempts
                    ], 401);
                }
            } catch (\Exception $e) {
                Log::error('2FA verification error', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid 2FA code'
                ], 401);
            }
        }

        // Clear failed authentication attempts on successful login
        RateLimitMiddleware::clearAuthAttempts($request);

        // Create API token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Handle remember device
        if ($request->remember_device) {
            $deviceId = $request->header('User-Agent', 'unknown') . '_' . $request->ip();
            $trustedDevices = $user->trusted_devices ?? [];
            $trustedDevices[] = [
                'device_id' => $deviceId,
                'added_at' => now()->toISOString(),
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip()
            ];
            $user->update(['trusted_devices' => $trustedDevices]);
        }

        $user->update(['remember_device' => $request->boolean('remember_device', false)]);

        // Log successful login with notification service
        NotificationService::logLogin($user, $user->role === 'admin');

        // Log successful login
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 200),
            'remember_device' => $request->boolean('remember_device', false),
            '2fa_used' => $user->hasTwoFactorEnabled()
        ]);

        // Debug: Log verification status
        Log::info('User login verification status', [
            'user_id' => $user->id,
            'email' => $user->email,
            'email_verified' => $user->isEmailVerified(),
            'phone_verified' => $user->isPhoneVerified(),
            'kyc_verified' => $user->isKycVerified(),
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'fully_verified' => $user->isFullyVerified(),
            'verification_progress' => $user->verification_progress,
            'requires_verification' => !$user->isFullyVerified()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load(['products', 'businesses', 'investments']),
                'token' => $token,
                'token_type' => 'Bearer',
                'verification_progress' => $user->verification_progress,
                'requires_verification' => !$user->isFullyVerified()
            ]
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->load(['products', 'businesses', 'investments']),
                'verification_progress' => $user->verification_progress,
                'verification_status' => [
                    'email_verified' => $user->isEmailVerified(),
                    'phone_verified' => $user->isPhoneVerified(),
                    'kyc_verified' => $user->isKycVerified(),
                    'kyc_submitted' => $user->hasSubmittedKyc(),
                    'kyc_status' => $user->kyc_status,
                    'two_factor_enabled' => $user->hasTwoFactorEnabled(),
                    'fully_verified' => $user->isFullyVerified()
                ]
            ]
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:255',
            'state' => 'sometimes|nullable|string|max:255',
            'country' => 'sometimes|nullable|string|max:255',
            'postal_code' => 'sometimes|nullable|string|max:20',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'bio' => 'sometimes|nullable|string|max:1000',
            'avatar_url' => 'nullable|url',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'first_name', 'last_name', 'phone', 'address', 'city', 'state', 
            'country', 'postal_code', 'date_of_birth', 'bio', 'avatar_url'
        ]);
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            try {
                $image = $request->file('profile_image');
                $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('profiles/' . $user->id, $filename, 'public');
                $updateData['avatar_url'] = config('app.url') . '/storage/' . $path;
                
                // Delete old profile image if exists
                if ($user->avatar_url && !str_contains($user->avatar_url, 'default')) {
                    $oldPath = str_replace('/storage/', '', $user->avatar_url);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Profile image upload failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload profile image'
                ], 500);
            }
        }
        
        // Update full name if first_name or last_name changed
        if (isset($updateData['first_name']) || isset($updateData['last_name'])) {
            $firstName = $updateData['first_name'] ?? $user->first_name;
            $lastName = $updateData['last_name'] ?? $user->last_name;
            $updateData['name'] = $firstName . ' ' . $lastName;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Upload profile image only
     */
    public function uploadProfileImage(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('profile_image');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profiles/' . $user->id, $filename, 'public');
            $avatarUrl = config('app.url') . '/storage/' . $path;
            
            // Delete old profile image if exists
            if ($user->avatar_url && !str_contains($user->avatar_url, 'default')) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
            
            $user->update(['avatar_url' => $avatarUrl]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                    'user' => $user,
                    'avatar_url' => $avatarUrl
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Profile image upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image'
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_products' => $user->products()->count(),
            'total_businesses' => $user->businesses()->count(),
            'total_investments' => $user->investments()->count(),
            'total_earnings' => $user->total_earnings ?? 0,
            'total_invested' => $user->total_invested ?? 0,
            'unread_messages' => $user->receivedMessages()->where('is_read', false)->count(),
            'verification_progress' => $user->verification_progress,
            'trust_score' => $user->trust_score
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Send password reset link to user's email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'If a user with that email address exists, we will send a password reset link.'
                ], 200);
            }

            // Generate password reset token
            $token = Str::random(60);
            $user->update([
                'password_reset_token' => $token,
                'password_reset_expires_at' => now()->addHours(24),
            ]);

            // Send password reset email
            $this->sendPasswordResetEmail($user, $token);

            Log::info('Password reset email sent', [
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'If a user with that email address exists, we will send a password reset link.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ], 500);
        }
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)
                ->where('password_reset_token', $request->token)
                ->where('password_reset_expires_at', '>', now())
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token.'
                ], 422);
            }

            // Update password and clear reset token
            $user->update([
                'password' => Hash::make($request->password),
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ]);

            Log::info('Password reset successful', [
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting your password.'
            ], 500);
        }
    }

    /**
     * Send password reset email.
     */
    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $resetUrl = config('app.frontend_url', 'https://bizinvestify.test') . '/auth/reset-password?email=' . urlencode($user->email) . '&token=' . $token;

        Mail::send('emails.password-reset', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'token' => $token,
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Reset Your Password - BizInvestify');
        });
    }

    /**
     * Get user's KYC application.
     */
    public function getMyKyc(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $kyc = \App\Models\Kyc::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => $kyc
        ]);
    }

    /**
     * Submit KYC application.
     */
    public function submitKyc(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|in:passport,national_id,drivers_license',
            'document_number' => 'required|string|max:255',
            'document_front' => 'required|file|image|max:5120',
            'document_back' => 'required|file|image|max:5120',
            'selfie' => 'required|file|image|max:5120',
            'proof_of_address' => 'required|file|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already has a KYC application
        $existingKyc = \App\Models\Kyc::where('user_id', $user->id)->first();
        if ($existingKyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC application already exists'
            ], 400);
        }

        try {
            // Handle file uploads
            $documentFrontPath = $request->file('document_front')->store('kyc/documents', 'public');
            $documentBackPath = $request->file('document_back')->store('kyc/documents', 'public');
            $selfiePath = $request->file('selfie')->store('kyc/selfies', 'public');
            $proofOfAddressPath = $request->file('proof_of_address')->store('kyc/address', 'public');

            $kyc = \App\Models\Kyc::create([
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'document_front_path' => $documentFrontPath,
                'document_back_path' => $documentBackPath,
                'selfie_path' => $selfiePath,
                'address_proof_path' => $proofOfAddressPath,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KYC application submitted successfully',
                'data' => $kyc
            ], 201);
        } catch (\Exception $e) {
            Log::error('KYC submission failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit KYC application'
            ], 500);
        }
    }

    /**
     * Verify two-factor authentication.
     */
    public function verify2FA(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($request->secret, $request->code)) {
            $user->update([
                'two_factor_secret' => $request->secret,
                'two_factor_confirmed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication enabled successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code'
        ], 400);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable2FA(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication disabled successfully'
        ]);
    }

    /**
     * Verify 2FA code for authenticated users (post-login verification).
     */
    public function verify2FAForSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled for this account'
            ], 400);
        }

        try {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if ($valid) {
                // Mark 2FA as verified for this session
                $sessionKey = '2fa_verified_' . $user->id;
                $request->session()->put($sessionKey, true);

                Log::info('2FA verification completed for session', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor authentication verified successfully',
                    'data' => [
                        'user_id' => $user->id,
                        'verification_complete' => true
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);

        } catch (\Exception $e) {
            Log::error('2FA session verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if 2FA verification is required for the current session.
     */
    public function check2FAVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'requires_2fa_verification' => false
                ]
            ]);
        }

        $sessionKey = '2fa_verified_' . $user->id;
        // In stateless API contexts, a session store may not be available
        $isVerified = $request->hasSession() ? $request->session()->has($sessionKey) : false;

        return response()->json([
            'success' => true,
            'data' => [
                'requires_2fa_verification' => !$isVerified,
                'user_id' => $user->id
            ]
        ]);
    }
}
