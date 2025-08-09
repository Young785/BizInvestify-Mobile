<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

class TestFrontend2FAFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:frontend-2fa-flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the frontend 2FA flow and API responses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Frontend 2FA Flow');
        $this->info('==========================');
        $this->newLine();

        // 1. Test API calls that should be allowed without 2FA verification
        $this->info('1. Testing API calls that should be allowed:');

        $allowedEndpoints = [
            '/api/check-2fa-status',
            '/api/verify-2fa-session',
            '/api/me'
        ];

        foreach ($allowedEndpoints as $endpoint) {
            $this->info("Testing endpoint: {$endpoint}");
            
            // Simulate API call
            $request = Request::create($endpoint, 'GET');
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
            
            // Set authenticated user
            $user = User::where('email', 'test@example.com')->first();
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            // Create middleware instance
            $middleware = new TwoFactorMiddleware();
            
            // Test middleware
            try {
                $response = $middleware->handle($request, function ($request) {
                    return response()->json(['success' => true, 'message' => 'Allowed']);
                });
                
                $this->info("  ✅ Allowed");
            } catch (\Exception $e) {
                $this->error("  ❌ Blocked: " . $e->getMessage());
            }
        }

        $this->newLine();

        // 2. Test API calls that should require 2FA verification
        $this->info('2. Testing API calls that should require 2FA verification:');

        $protectedEndpoints = [
            '/api/profile',
            '/api/stats',
            '/api/products',
            '/api/businesses'
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $this->info("Testing endpoint: {$endpoint}");
            
            // Simulate API call
            $request = Request::create($endpoint, 'GET');
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
            
            // Set authenticated user
            $user = User::where('email', 'test@example.com')->first();
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            // Create middleware instance
            $middleware = new TwoFactorMiddleware();
            
            // Test middleware
            try {
                $response = $middleware->handle($request, function ($request) {
                    return response()->json(['success' => true, 'message' => 'Allowed']);
                });
                
                $this->warn("  ⚠️  Allowed (should be blocked)");
            } catch (\Exception $e) {
                $this->info("  ✅ Blocked as expected: " . $e->getMessage());
            }
        }

        $this->newLine();

        // 3. Test 2FA verification flow
        $this->info('3. Testing 2FA verification flow:');

        // Simulate successful 2FA verification
        $request = Request::create('/api/verify-2fa-session', 'POST', ['code' => '123456']);
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
        $controller = new AuthController();

        try {
            $response = $controller->verify2FAForSession($request);
            $data = $response->getData();
            
            if ($data->success) {
                $this->info("  ✅ 2FA verification successful");
                $this->info("  Session key should be set: 2fa_verified_{$user->id}");
            } else {
                $this->error("  ❌ 2FA verification failed: {$data->message}");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('Test completed!');
        
        return Command::SUCCESS;
    }
} 