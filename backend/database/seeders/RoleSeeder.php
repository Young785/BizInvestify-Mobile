<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $seller = Role::firstOrCreate(['name' => 'seller']);
        $buyer = Role::firstOrCreate(['name' => 'buyer']);
        $investor = Role::firstOrCreate(['name' => 'investor']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $support = Role::firstOrCreate(['name' => 'support']);

        // Assign permissions to roles
        $this->assignPermissions($superAdmin, $admin, $seller, $buyer, $investor, $moderator, $support);
    }

    private function assignPermissions($superAdmin, $admin, $seller, $buyer, $investor, $moderator, $support): void
    {
        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin permissions
        $adminPermissions = [
            // Admin access
            'admin.access', 'admin.dashboard', 'admin.analytics', 'admin.system_health', 'admin.system_logs', 'admin.settings',
            
            // User management
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.export', 'users.suspend', 'users.activate',
            
            // KYC management
            'kyc.view', 'kyc.approve', 'kyc.reject', 'kyc.documents', 'kyc.bulk_approve', 'kyc.bulk_reject', 'kyc.export',
            
            // Content moderation
            'moderation.manage', 'moderation.reviews', 'moderation.disputes', 'moderation.appeals', 'moderation.bulk_actions',
            
            // Transaction management
            'admin.transactions', 'transactions.manage', 'transactions.refund', 'transactions.export', 'transactions.analytics',
            'payments.manage', 'payments.view', 'payments.export',
            
            // Reports
            'reports.view', 'reports.export', 'reports.custom', 'reports.scheduled', 'reports.analytics',
            
            // Analytics
            'analytics.view', 'analytics.export', 'analytics.custom_reports', 'analytics.real_time', 'analytics.tracking',
            
            // Activity logs
            'activity_logs.view', 'activity_logs.export', 'activity_logs.analytics',
            
            // Notifications
            'notifications.view', 'notifications.send', 'notifications.manage', 'notifications.templates', 'notifications.bulk_send',
            
            // Support
            'support.view', 'support.respond', 'support.escalate', 'support.analytics',
            
            // Security
            'security.view', 'security.manage', 'security.audit', 'security.2fa_manage',
            
            // Settings
            'system.settings', 'settings.view', 'settings.edit', 'settings.system', 'settings.email', 'settings.payment', 'settings.notifications',
            
            // Monitoring
            'monitoring.view', 'monitoring.alerts', 'monitoring.metrics', 'monitoring.logs',
            
            // Audit
            'audit.view', 'audit.export', 'audit.analytics',
            
            // Bulk operations
            'bulk_actions.execute', 'bulk_actions.schedule', 'bulk_actions.monitor',
            
            // Export/Import
            'export.data', 'import.data', 'export.schedule', 'import.schedule',
            
            // Maintenance
            'maintenance.view', 'maintenance.execute', 'maintenance.schedule',
        ];
        $admin->givePermissionTo($adminPermissions);

        // Seller permissions
        $sellerPermissions = [
            // Product management
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.export',
            
            // Business management
            'businesses.view', 'businesses.create', 'businesses.edit', 'businesses.delete', 'businesses.export',
            
            // Investment management
            'investments.view', 'investments.create', 'investments.manage',
            
            // Transaction management
            'transactions.view', 'transactions.create', 'transactions.cancel', 'transactions.export',
            
            // Payment management
            'payments.create', 'payments.view', 'payments.export',
            
            // Messaging
            'messages.view', 'messages.send', 'messages.export',
            
            // Activity logs
            'activity_logs.view', 'activity_logs.export',
            
            // Notifications
            'notifications.view', 'notifications.send',
            
            // Analytics
            'analytics.view', 'analytics.export',
            
            // Marketplace
            'marketplace.view', 'marketplace.manage', 'marketplace.featured',
            
            // Reviews
            'reviews.view', 'reviews.create', 'reviews.edit', 'reviews.delete',
            
            // Support
            'support.view', 'support.create',
            
            // Featured listings
            'featured_listings.view', 'featured_listings.create', 'featured_listings.edit', 'featured_listings.delete',
            
            // Conversations
            'conversations.view', 'conversations.create', 'conversations.manage',
            
            // Chat
            'chat.view', 'chat.send',
            
            // Search
            'search.view', 'search.advanced',
            
            // Wishlist
            'wishlist.view', 'wishlist.manage',
        ];
        $seller->givePermissionTo($sellerPermissions);

        // Buyer permissions
        $buyerPermissions = [
            // Product viewing
            'products.view',
            
            // Business viewing
            'businesses.view',
            
            // Investment management
            'investments.view', 'investments.create',
            
            // Transaction management
            'transactions.view', 'transactions.create', 'transactions.cancel',
            
            // Payment management
            'payments.create', 'payments.view',
            
            // Messaging
            'messages.view', 'messages.send',
            
            // Activity logs
            'activity_logs.view',
            
            // Notifications
            'notifications.view', 'notifications.send',
            
            // Analytics
            'analytics.view',
            
            // Marketplace
            'marketplace.view', 'marketplace.search',
            
            // Reviews
            'reviews.view', 'reviews.create', 'reviews.edit', 'reviews.delete',
            
            // Support
            'support.view', 'support.create',
            
            // Conversations
            'conversations.view', 'conversations.create',
            
            // Chat
            'chat.view', 'chat.send',
            
            // Search
            'search.view', 'search.advanced',
            
            // Wishlist
            'wishlist.view', 'wishlist.manage', 'wishlist.share',
            
            // Comparison
            'comparison.view', 'comparison.create', 'comparison.share',
            
            // Recommendations
            'recommendations.view',
        ];
        $buyer->givePermissionTo($buyerPermissions);

        // Investor permissions (similar to buyer but with investment focus)
        $investorPermissions = [
            // Product viewing
            'products.view',
            
            // Business viewing and investment
            'businesses.view',
            
            // Investment management
            'investments.view', 'investments.create', 'investments.manage',
            
            // Transaction management
            'transactions.view', 'transactions.create', 'transactions.cancel',
            
            // Payment management
            'payments.create', 'payments.view',
            
            // Messaging
            'messages.view', 'messages.send',
            
            // Activity logs
            'activity_logs.view',
            
            // Notifications
            'notifications.view', 'notifications.send',
            
            // Analytics
            'analytics.view',
            
            // Marketplace
            'marketplace.view', 'marketplace.search',
            
            // Support
            'support.view', 'support.create',
            
            // Conversations
            'conversations.view', 'conversations.create',
            
            // Chat
            'chat.view', 'chat.send',
            
            // Search
            'search.view', 'search.advanced',
            
            // Recommendations
            'recommendations.view',
        ];
        $investor->givePermissionTo($investorPermissions);

        // Moderator permissions
        $moderatorPermissions = [
            // Content moderation
            'moderation.manage', 'moderation.reviews', 'moderation.disputes', 'moderation.appeals', 'moderation.bulk_actions',
            
            // Product moderation
            'products.view', 'products.moderate',
            
            // Business moderation
            'businesses.view', 'businesses.moderate',
            
            // Review moderation
            'reviews.view', 'reviews.moderate', 'reviews.export',
            
            // Messaging moderation
            'messages.view', 'messages.moderate', 'messages.export',
            
            // Chat moderation
            'chat.view', 'chat.moderate',
            
            // Activity logs
            'activity_logs.view', 'activity_logs.export',
            
            // Notifications
            'notifications.view', 'notifications.send',
            
            // Support
            'support.view', 'support.respond',
            
            // Analytics
            'analytics.view',
            
            // Reports
            'reports.view', 'reports.export',
        ];
        $moderator->givePermissionTo($moderatorPermissions);

        // Support permissions
        $supportPermissions = [
            // Support management
            'support.view', 'support.create', 'support.respond', 'support.escalate', 'support.analytics',
            
            // User management (limited)
            'users.view',
            
            // Transaction viewing
            'transactions.view',
            
            // Messaging
            'messages.view', 'messages.send',
            
            // Activity logs
            'activity_logs.view',
            
            // Notifications
            'notifications.view', 'notifications.send',
            
            // Analytics
            'analytics.view',
            
            // Reports
            'reports.view',
        ];
        $support->givePermissionTo($supportPermissions);

        $this->command->info('✅ All roles and permissions assigned successfully!');
    }
}