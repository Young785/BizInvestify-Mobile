<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run permission and role seeders first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // Create Super Admin user
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@bizinvestify.com'],
            [
                'name' => 'Super Administrator',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 5.00,
            ]
        );

        // Assign the super_admin role to the user
        if ($superAdminRole && !$superAdmin->hasRole($superAdminRole)) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Create Test User
        $buyerRole = Role::where('name', 'buyer')->first();
        
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'email_verified_at' => now(),
            ]
        );

        // Assign the buyer role to the user
        if ($buyerRole && !$testUser->hasRole($buyerRole)) {
            $testUser->assignRole($buyerRole);
        }
    }
}
