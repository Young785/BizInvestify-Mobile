<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

// Test 2FA API with valid token
echo "Testing 2FA API with valid token\n";
echo "=================================\n\n";

// Get test user
$user = User::where('email', 'test@example.com')->first();

if (!$user) {
    echo "Test user not found. Creating...\n";
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
echo "2FA Enabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No') . "\n\n";

// Create a token for the user
$token = $user->createToken('test-token')->plainTextToken;
echo "Created token: {$token}\n\n";

// Test API call with token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "API Response (HTTP {$httpCode}):\n";
echo $response . "\n\n";

// Test another endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/stats");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Stats API Response (HTTP {$httpCode}):\n";
echo $response . "\n\n";

echo "Test completed!\n"; 