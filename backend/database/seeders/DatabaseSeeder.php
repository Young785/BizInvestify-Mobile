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
        $this->command->info('🚀 Starting database seeding...');

        // Run permission and role seeders first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->command->info('✅ Permissions and roles created successfully!');

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
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 5.00,
                'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
                'two_factor_confirmed_at' => now(),
            ]
        );

        // Assign the super_admin role to the user
        if ($superAdminRole && !$superAdmin->hasRole($superAdminRole)) {
            $superAdmin->assignRole($superAdminRole);
            $this->command->info('✅ Super Admin user created and role assigned!');
        }

        // Create Admin user
        $adminRole = Role::where('name', 'admin')->first();
        
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 4.50,
            ]
        );

        if ($adminRole && !$admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
            $this->command->info('✅ Admin user created and role assigned!');
        }

        // Create Seller user
        $sellerRole = Role::where('name', 'seller')->first();
        
        $seller = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Seller User',
                'first_name' => 'Seller',
                'last_name' => 'User',
                'password' => Hash::make('Seller@123'),
                'role' => 'seller',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 4.00,
            ]
        );

        if ($sellerRole && !$seller->hasRole($sellerRole)) {
            $seller->assignRole($sellerRole);
            $this->command->info('✅ Seller user created and role assigned!');
        }

        // Create Buyer user
        $buyerRole = Role::where('name', 'buyer')->first();
        
        $buyer = User::firstOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Buyer User',
                'first_name' => 'Buyer',
                'last_name' => 'User',
                'password' => Hash::make('Buyer@123'),
                'role' => 'buyer',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 3.50,
            ]
        );

        if ($buyerRole && !$buyer->hasRole($buyerRole)) {
            $buyer->assignRole($buyerRole);
            $this->command->info('✅ Buyer user created and role assigned!');
        }

        // Create Investor user
        $investorRole = Role::where('name', 'investor')->first();
        
        $investor = User::firstOrCreate(
            ['email' => 'investor@example.com'],
            [
                'name' => 'Investor User',
                'first_name' => 'Investor',
                'last_name' => 'User',
                'password' => Hash::make('Investor@123'),
                'role' => 'investor',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 4.25,
            ]
        );

        if ($investorRole && !$investor->hasRole($investorRole)) {
            $investor->assignRole($investorRole);
            $this->command->info('✅ Investor user created and role assigned!');
        }

        // Create Moderator user
        $moderatorRole = Role::where('name', 'moderator')->first();
        
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Moderator User',
                'first_name' => 'Moderator',
                'last_name' => 'User',
                'password' => Hash::make('Moderator@123'),
                'role' => 'moderator',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 4.75,
            ]
        );

        if ($moderatorRole && !$moderator->hasRole($moderatorRole)) {
            $moderator->assignRole($moderatorRole);
            $this->command->info('✅ Moderator user created and role assigned!');
        }

        // Create Support user
        $supportRole = Role::where('name', 'support')->first();
        
        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Support User',
                'first_name' => 'Support',
                'last_name' => 'User',
                'password' => Hash::make('Support@123'),
                'role' => 'support',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 4.50,
            ]
        );

        if ($supportRole && !$support->hasRole($supportRole)) {
            $support->assignRole($supportRole);
            $this->command->info('✅ Support user created and role assigned!');
        }

        // Create Test User with 2FA enabled
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password123'),
                'role' => 'buyer',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_verified' => true,
                'kyc_status' => 'verified',
                'trust_score' => 3.00,
                'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
                'two_factor_confirmed_at' => now(),
            ]
        );

        if ($buyerRole && !$testUser->hasRole($buyerRole)) {
            $testUser->assignRole($buyerRole);
            $this->command->info('✅ Test user created and role assigned!');
        }

        // Run sample data seeder
        $this->call([
            SampleDataSeeder::class,
        ]);

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📋 Created Users:');
        $this->command->info('   Super Admin: admin@bizinvestify.com (SuperAdmin@123)');
        $this->command->info('   Admin: admin@example.com (Admin@123)');
        $this->command->info('   Seller: seller@example.com (Seller@123)');
        $this->command->info('   Buyer: buyer@example.com (Buyer@123)');
        $this->command->info('   Investor: investor@example.com (Investor@123)');
        $this->command->info('   Moderator: moderator@example.com (Moderator@123)');
        $this->command->info('   Support: support@example.com (Support@123)');
        $this->command->info('   Test User: test@example.com (password123) - 2FA Enabled');
    }
}
