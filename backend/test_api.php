<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AdminController;
use App\Models\User;

// Create a test user for admin
$user = User::where('role', 'admin')->first();

if (!$user) {
    echo "No admin user found. Creating one...\n";
    $user = User::create([
        'name' => 'Admin User',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@bizinvestify.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'is_verified' => true,
        'email_verified_at' => now(),
    ]);
}

echo "Testing admin API endpoints...\n";

// Test getUsers endpoint
$request = new Request();
$controller = new AdminController();

try {
    $response = $controller->getUsers($request);
    echo "getUsers response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "getUsers error: " . $e->getMessage() . "\n";
}

// Test suspendUser endpoint
try {
    $testUser = User::where('role', '!=', 'admin')->first();
    if ($testUser) {
        $response = $controller->suspendUser($testUser);
        echo "suspendUser response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
        
        // Reactivate the user
        $response = $controller->activateUser($testUser);
        echo "activateUser response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "No non-admin user found to test suspend/activate\n";
    }
} catch (Exception $e) {
    echo "suspendUser/activateUser error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n"; 