<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

// Find the user
$user = User::where('email', 'ayomikunariyo@gmail.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User found: {$user->email}\n";
echo "Email verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
echo "Current token: " . ($user->email_verification_token ?? 'null') . "\n";
echo "Token expires: " . ($user->email_verification_expires_at ?? 'null') . "\n\n";

// Generate a new token
$newToken = $user->generateEmailVerificationToken();

echo "New token generated: {$newToken}\n";
echo "New expiry: {$user->email_verification_expires_at}\n\n";

// Generate verification URL
$verificationUrl = config('app.frontend_url', 'http://localhost:3000')
    . '/auth/verify-email/verify-token?token=' . $newToken . '&email=' . urlencode($user->email);

echo "Verification URL:\n{$verificationUrl}\n\n";

// Log this for reference
Log::info('Manual verification token generated', [
    'user_id' => $user->id,
    'email' => $user->email,
    'token' => $newToken,
    'url' => $verificationUrl
]);

echo "✅ New verification link generated successfully!\n";
echo "Please use the URL above to verify your email.\n";
