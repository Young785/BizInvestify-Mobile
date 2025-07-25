<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AdminController;
use App\Models\User;

class TestAdminApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test admin API endpoints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing admin API endpoints...');

        // Create a test user for admin if none exists
        $adminUser = User::where('role', 'admin')->first();

        if (!$adminUser) {
            $this->info('No admin user found. Creating one...');
            $adminUser = User::create([
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@bizinvestify.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);
            $this->info('Admin user created with ID: ' . $adminUser->id);
        }

        $controller = new AdminController();

        // Test getUsers endpoint
        $this->info('Testing getUsers endpoint...');
        try {
            $request = new Request();
            $response = $controller->getUsers($request);
            $data = $response->getData();
            
            if ($data->success) {
                $this->info('✅ getUsers endpoint working');
                $this->info('Total users: ' . ($data->data->total ?? 'N/A'));
            } else {
                $this->error('❌ getUsers endpoint failed: ' . ($data->message ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error('❌ getUsers error: ' . $e->getMessage());
        }

        // Test suspendUser endpoint
        $this->info('Testing suspendUser endpoint...');
        try {
            $testUser = User::where('role', '!=', 'admin')->first();
            if ($testUser) {
                $response = $controller->suspendUser($testUser);
                $data = $response->getData();
                
                if ($data->success) {
                    $this->info('✅ suspendUser endpoint working');
                    
                    // Reactivate the user
                    $response = $controller->activateUser($testUser);
                    $data = $response->getData();
                    
                    if ($data->success) {
                        $this->info('✅ activateUser endpoint working');
                    } else {
                        $this->error('❌ activateUser endpoint failed: ' . ($data->message ?? 'Unknown error'));
                    }
                } else {
                    $this->error('❌ suspendUser endpoint failed: ' . ($data->message ?? 'Unknown error'));
                }
            } else {
                $this->warn('No non-admin user found to test suspend/activate');
            }
        } catch (\Exception $e) {
            $this->error('❌ suspendUser/activateUser error: ' . $e->getMessage());
        }

        // Test deleteUser endpoint
        $this->info('Testing deleteUser endpoint...');
        try {
            $testUser = User::where('role', '!=', 'admin')->first();
            if ($testUser) {
                $response = $controller->deleteUser($testUser);
                $data = $response->getData();
                
                if ($data->success) {
                    $this->info('✅ deleteUser endpoint working');
                } else {
                    $this->error('❌ deleteUser endpoint failed: ' . ($data->message ?? 'Unknown error'));
                }
            } else {
                $this->warn('No non-admin user found to test delete');
            }
        } catch (\Exception $e) {
            $this->error('❌ deleteUser error: ' . $e->getMessage());
        }

        $this->info('Test completed.');
    }
} 