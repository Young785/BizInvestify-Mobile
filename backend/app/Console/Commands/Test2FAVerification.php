<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Test2FAVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:2fa-verification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the 2FA verification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing 2FA Verification System');
        $this->info('================================');
        $this->newLine();

        // 1. Test User with 2FA Enabled
        $this->info('1. Testing user with 2FA enabled:');
        
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->info('Creating test user...');
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
        } else {
            // Update existing user to have 2FA enabled
            $user->update([
                'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
                'two_factor_confirmed_at' => now(),
            ]);
            // Refresh the user model to get updated data
            $user->refresh();
        }

        $this->info("User ID: {$user->id}");
        $this->info("Email: {$user->email}");
        $this->info("2FA Secret: " . ($user->two_factor_secret ? 'Set' : 'Not Set'));
        $this->info("2FA Confirmed: " . ($user->two_factor_confirmed_at ? 'Yes' : 'No'));
        $this->info("2FA Enabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No'));
        $this->newLine();

        // 2. Test 2FA Verification
        $this->info('2. Testing 2FA verification:');

        // Simulate a request with authenticated user
        $request = new Request();
        $request->merge(['code' => '123456']);
        
        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Set up session for the request
        $request->setLaravelSession(app('session.store'));

        // Create controller instance
        $controller = new AuthController();

        // Test verification
        try {
            $response = $controller->verify2FAForSession($request);
            $this->info("Response: " . json_encode($response->getData(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        $this->newLine();

        // 3. Test 2FA Status Check
        $this->info('3. Testing 2FA status check:');

        try {
            $response = $controller->check2FAVerificationStatus($request);
            $this->info("Status Response: " . json_encode($response->getData(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        $this->newLine();

        // 4. Test User Verification Methods
        $this->info('4. Testing user verification methods:');
        $this->info("isEmailVerified: " . ($user->isEmailVerified() ? 'Yes' : 'No'));
        $this->info("isPhoneVerified: " . ($user->isPhoneVerified() ? 'Yes' : 'No'));
        $this->info("hasTwoFactorEnabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No'));
        $this->info("isFullyVerified: " . ($user->isFullyVerified() ? 'Yes' : 'No'));
        $this->info("Verification Progress: {$user->verification_progress}%");
        $this->newLine();

        // 5. Test Middleware Logic
        $this->info('5. Testing middleware logic:');
        
        // Simulate session
        $session = app('session');
        $sessionKey = '2fa_verified_' . $user->id;
        $session->put($sessionKey, true);

        $this->info("Session key: {$sessionKey}");
        $this->info("Session has key: " . ($session->has($sessionKey) ? 'Yes' : 'No'));
        $this->newLine();

        $this->info('Test completed!');
        
        return Command::SUCCESS;
    }
} 