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
            'businessName' => 'required_if:role,seller|string|max:255',
            'businessType' => 'required_if:role,seller|string|max:255',
            'investmentAmount' => 'required_if:role,investor|string|max:255',
            'investmentFocus' => 'required_if:role,investor|string|max:255',
            
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
                'investment_amount' => $request->investmentAmount,
                'investment_focus' => $request->investmentFocus,
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
                    'success' => false,
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
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'avatar_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['first_name', 'last_name', 'phone', 'avatar_url']);
        
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
}
