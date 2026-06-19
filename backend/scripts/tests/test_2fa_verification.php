<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\Api\AuthController;

// Test 2FA Verification System
echo "Testing 2FA Verification System\n";
echo "================================\n\n";

// 1. Test User with 2FA Enabled
echo "1. Testing user with 2FA enabled:\n";
$user = User::where('email', 'test@example.com')->first();

if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'investor',
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_confirmed_at' => now(),
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
    ]);
}

echo "User ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "2FA Enabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No') . "\n";
echo "2FA Confirmed: " . ($user->two_factor_confirmed_at ? 'Yes' : 'No') . "\n\n";

// 2. Test 2FA Verification
echo "2. Testing 2FA verification:\n";

// Simulate a request
$request = new \Illuminate\Http\Request();
$request->merge(['code' => '123456']);

// Create controller instance
$controller = new AuthController();

// Test verification
try {
    $response = $controller->verify2FAForSession($request);
    echo "Response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// 3. Test 2FA Status Check
echo "3. Testing 2FA status check:\n";

try {
    $response = $controller->check2FAVerificationStatus($request);
    echo "Status Response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// 4. Test Middleware Logic
echo "4. Testing middleware logic:\n";

// Simulate session
$session = new \Illuminate\Session\Store('test', new \Illuminate\Session\FileSessionHandler(
    new \Illuminate\Filesystem\Filesystem(),
    storage_path('framework/sessions'),
    60
));

$sessionKey = '2fa_verified_' . $user->id;
$session->put($sessionKey, true);

echo "Session key: {$sessionKey}\n";
echo "Session has key: " . ($session->has($sessionKey) ? 'Yes' : 'No') . "\n\n";

// 5. Test User Verification Methods
echo "5. Testing user verification methods:\n";
echo "isEmailVerified: " . ($user->isEmailVerified() ? 'Yes' : 'No') . "\n";
echo "isPhoneVerified: " . ($user->isPhoneVerified() ? 'Yes' : 'No') . "\n";
echo "hasTwoFactorEnabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No') . "\n";
echo "isFullyVerified: " . ($user->isFullyVerified() ? 'Yes' : 'No') . "\n";
echo "Verification Progress: {$user->verification_progress}%\n\n";

echo "Test completed!\n"; 