<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Test2FAApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:2fa-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test 2FA API with valid token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing 2FA API with valid token');
        $this->info('=================================');
        $this->newLine();

        // Get test user
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->info('Test user not found. Creating...');
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

        $this->info("User ID: {$user->id}");
        $this->info("Email: {$user->email}");
        $this->info("2FA Enabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No'));
        $this->newLine();

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;
        $this->info("Created token: {$token}");
        $this->newLine();

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

        $this->info("API Response (HTTP {$httpCode}):");
        $this->line($response);
        $this->newLine();

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

        $this->info("Stats API Response (HTTP {$httpCode}):");
        $this->line($response);
        $this->newLine();

        $this->info('Test completed!');
        
        return Command::SUCCESS;
    }
} 