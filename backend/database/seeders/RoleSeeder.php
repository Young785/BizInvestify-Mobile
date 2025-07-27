<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions first
        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
            'users.export',
            
            // Product management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Business management
            'businesses.view',
            'businesses.create',
            'businesses.edit',
            'businesses.delete',
            
            // Investment management
            'investments.view',
            'investments.create',
            'investments.approve',
            
            // Transaction management
            'transactions.view',
            'transactions.create',
            'transactions.cancel',
            'admin.transactions',
            
            // KYC management
            'kyc.view',
            'kyc.approve',
            'kyc.reject',
            
            // Analytics
            'analytics.view',
            'admin.analytics',
            
            // Reports
            'reports.view',
            
            // Content moderation
            'moderation.manage',
            
            // System settings
            'system.settings',
            
            // Admin access
            'admin.access',
            
            // Super admin access
            'super_admin.access',
            
            // Messages
            'messages.view',
            'messages.send',
            
            // Activity logs
            'activity_logs.view',
            
            // Notifications
            'notifications.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $seller = Role::firstOrCreate(['name' => 'seller']);
        $buyer = Role::firstOrCreate(['name' => 'buyer']);

        // Assign permissions to roles
        $this->assignPermissions($superAdmin, $admin, $seller, $buyer);
    }

    private function assignPermissions($superAdmin, $admin, $seller, $buyer): void
    {
        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin permissions
        $adminPermissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'admin.transactions', 'admin.analytics',
            'reports.view', 'moderation.manage', 'system.settings',
            'admin.access', 'messages.view', 'messages.send',
            'activity_logs.view', 'notifications.view',
            'payments.create', 'payments.refund', 'payments.view'
        ];
        $admin->givePermissionTo($adminPermissions);

        // Seller permissions
        $sellerPermissions = [
            'products.view', 'products.create', 'products.edit',
            'businesses.view', 'businesses.create', 'businesses.edit',
            'investments.view', 'transactions.view',
            'messages.view', 'messages.send', 'activity_logs.view',
            'payments.create', 'payments.view'
        ];
        $seller->givePermissionTo($sellerPermissions);

        // Buyer permissions
        $buyerPermissions = [
            'products.view', 'businesses.view',
            'investments.view', 'investments.create',
            'transactions.view', 'messages.view', 'messages.send',
            'activity_logs.view',
            'payments.create', 'payments.view'
        ];
        $buyer->givePermissionTo($buyerPermissions);
    }
}