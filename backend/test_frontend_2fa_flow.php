<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Test Frontend 2FA Flow
echo "Testing Frontend 2FA Flow\n";
echo "==========================\n\n";

// 1. Test API calls that should be allowed without 2FA verification
echo "1. Testing API calls that should be allowed:\n";

$allowedEndpoints = [
    '/api/check-2fa-status',
    '/api/verify-2fa-session',
    '/api/me'
];

foreach ($allowedEndpoints as $endpoint) {
    echo "Testing endpoint: {$endpoint}\n";
    
    // Simulate API call
    $request = \Illuminate\Http\Request::create($endpoint, 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');
    
    // Set authenticated user
    $user = User::where('email', 'test@example.com')->first();
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Create middleware instance
    $middleware = new \App\Http\Middleware\TwoFactorMiddleware();
    
    // Test middleware
    try {
        $response = $middleware->handle($request, function ($request) {
            return response()->json(['success' => true, 'message' => 'Allowed']);
        });
        
        echo "  ✅ Allowed\n";
    } catch (Exception $e) {
        echo "  ❌ Blocked: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 2. Test API calls that should require 2FA verification
echo "2. Testing API calls that should require 2FA verification:\n";

$protectedEndpoints = [
    '/api/profile',
    '/api/stats',
    '/api/products',
    '/api/businesses'
];

foreach ($protectedEndpoints as $endpoint) {
    echo "Testing endpoint: {$endpoint}\n";
    
    // Simulate API call
    $request = \Illuminate\Http\Request::create($endpoint, 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');
    
    // Set authenticated user
    $user = User::where('email', 'test@example.com')->first();
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Create middleware instance
    $middleware = new \App\Http\Middleware\TwoFactorMiddleware();
    
    // Test middleware
    try {
        $response = $middleware->handle($request, function ($request) {
            return response()->json(['success' => true, 'message' => 'Allowed']);
        });
        
        echo "  ✅ Allowed (should be blocked)\n";
    } catch (Exception $e) {
        echo "  ❌ Blocked: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 3. Test 2FA verification flow
echo "3. Testing 2FA verification flow:\n";

// Simulate successful 2FA verification
$request = \Illuminate\Http\Request::create('/api/verify-2fa-session', 'POST', ['code' => '123456']);
$request->headers->set('Accept', 'application/json');
$request->headers->set('Content-Type', 'application/json');

// Set authenticated user
$user = User::where('email', 'test@example.com')->first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Set up session
$request->setLaravelSession(app('session.store'));

// Create controller instance
$controller = new \App\Http\Controllers\Api\AuthController();

try {
    $response = $controller->verify2FAForSession($request);
    $data = $response->getData();
    
    if ($data->success) {
        echo "  ✅ 2FA verification successful\n";
        echo "  Session key should be set: 2fa_verified_{$user->id}\n";
    } else {
        echo "  ❌ 2FA verification failed: {$data->message}\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n"; 