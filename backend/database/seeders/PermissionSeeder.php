<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Super Admin Permissions
            'super_admin.access',
            'system.settings',
            'roles.manage',
            'permissions.manage',
            
            // Admin Permissions
            'admin.access',
            'admin.dashboard',
            'admin.analytics',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
            'users.export',
            
            // KYC Management
            'kyc.view',
            'kyc.approve',
            'kyc.reject',
            'kyc.documents',
            
            // Product Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.approve',
            
            // Business Management
            'businesses.view',
            'businesses.create',
            'businesses.edit',
            'businesses.delete',
            'businesses.approve',
            
            // Investment Management
            'investments.view',
            'investments.create',
            'investments.approve',
            'investments.manage',
            
            // Transaction Management
            'transactions.view',
            'transactions.create',
            'transactions.cancel',
            'admin.transactions',
            'transactions.manage',
            'transactions.refund',
            
            // Payment Management
            'payments.create',
            'payments.refund',
            'payments.view',
            
            // Communication
            'messages.view',
            'messages.send',
            'messages.moderate',
            
            // Activity Logs
            'activity_logs.view',
            'activity_logs.export',
            
            // Notifications
            'notifications.view',
            'notifications.send',
            'notifications.manage',
            
            // Reports
            'reports.view',
            
            // Content Moderation
            'moderation.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}