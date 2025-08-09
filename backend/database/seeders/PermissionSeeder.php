<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
             // ===== SUPER ADMIN PERMISSIONS =====
            'super_admin.access',
            'system.settings',
            'roles.manage',
            'permissions.manage',
            'system.maintenance',
            'system.backup',
            'system.restore',
            
            // ===== ADMIN PERMISSIONS =====
            'admin.access',
            'admin.dashboard',
            'admin.analytics',
            'admin.system_health',
            'admin.system_logs',
            'admin.settings',
            
            // ===== USER MANAGEMENT =====
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
            'users.export',
            'users.suspend',
            'users.activate',
            'users.bulk_actions',
            
            // ===== KYC MANAGEMENT =====
            'kyc.view',
            'kyc.approve',
            'kyc.reject',
            'kyc.documents',
            'kyc.bulk_approve',
            'kyc.bulk_reject',
            'kyc.export',
            
            // ===== PRODUCT MANAGEMENT =====
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.approve',
            'products.bulk_actions',
            'products.export',
            'products.moderate',
            
            // ===== BUSINESS MANAGEMENT =====
            'businesses.view',
            'businesses.create',
            'businesses.edit',
            'businesses.delete',
            'businesses.approve',
            'businesses.bulk_actions',
            'businesses.export',
            'businesses.moderate',
            
            // ===== INVESTMENT MANAGEMENT =====
            'investments.view',
            'investments.create',
            'investments.approve',
            'investments.manage',
            'investments.cancel',
            'investments.export',
            'investments.bulk_actions',
            
            // ===== TRANSACTION MANAGEMENT =====
            'transactions.view',
            'transactions.create',
            'transactions.cancel',
            'admin.transactions',
            'transactions.manage',
            'transactions.refund',
            'transactions.export',
            'transactions.bulk_actions',
            'transactions.analytics',
            
            // ===== PAYMENT MANAGEMENT =====
            'payments.create',
            'payments.refund',
            'payments.view',
            'payments.manage',
            'payments.export',
            'payments.analytics',
            'payments.webhooks',
            
            // ===== MESSAGING SYSTEM =====
            'messages.view',
            'messages.send',
            'messages.moderate',
            'messages.bulk_actions',
            'messages.export',
            'messages.analytics',
            
            // ===== ACTIVITY LOGS =====
            'activity_logs.view',
            'activity_logs.export',
            'activity_logs.analytics',
            'activity_logs.purge',
            
            // ===== NOTIFICATIONS =====
            'notifications.view',
            'notifications.send',
            'notifications.manage',
            'notifications.templates',
            'notifications.bulk_send',
            'notifications.analytics',
            
            // ===== REPORTS =====
            'reports.view',
            'reports.export',
            'reports.custom',
            'reports.scheduled',
            'reports.analytics',
            
            // ===== CONTENT MODERATION =====
            'moderation.manage',
            'moderation.reviews',
            'moderation.disputes',
            'moderation.appeals',
            'moderation.bulk_actions',
            
            // ===== ANALYTICS =====
            'analytics.view',
            'analytics.export',
            'analytics.custom_reports',
            'analytics.real_time',
            'analytics.tracking',
            
            // ===== MARKETPLACE =====
            'marketplace.view',
            'marketplace.manage',
            'marketplace.featured',
            'marketplace.categories',
            'marketplace.search',
            'marketplace.recommendations',
            
            // ===== COMPARISON TOOLS =====
            'comparison.view',
            'comparison.create',
            'comparison.share',
            'comparison.export',
            'comparison.analytics',
            
            // ===== WISHLIST =====
            'wishlist.view',
            'wishlist.manage',
            'wishlist.share',
            'wishlist.export',
            
            // ===== REVIEWS =====
            'reviews.view',
            'reviews.create',
            'reviews.edit',
            'reviews.delete',
            'reviews.moderate',
            'reviews.export',
            
            // ===== SUPPORT =====
            'support.view',
            'support.create',
            'support.respond',
            'support.escalate',
            'support.analytics',
            
            // ===== FEATURED LISTINGS =====
            'featured_listings.view',
            'featured_listings.create',
            'featured_listings.edit',
            'featured_listings.delete',
            'featured_listings.analytics',
            
            // ===== CONVERSATIONS =====
            'conversations.view',
            'conversations.create',
            'conversations.manage',
            'conversations.export',
            
            // ===== CHAT =====
            'chat.view',
            'chat.send',
            'chat.moderate',
            'chat.analytics',
            
            // ===== SEARCH =====
            'search.view',
            'search.advanced',
            'search.analytics',
            'search.recommendations',
            
            // ===== RECOMMENDATIONS =====
            'recommendations.view',
            'recommendations.manage',
            'recommendations.analytics',
            
            // ===== WEBHOOKS =====
            'webhooks.view',
            'webhooks.create',
            'webhooks.edit',
            'webhooks.delete',
            'webhooks.test',
            
            // ===== API MANAGEMENT =====
            'api.view',
            'api.create',
            'api.edit',
            'api.delete',
            'api.analytics',
            
            // ===== SECURITY =====
            'security.view',
            'security.manage',
            'security.audit',
            'security.2fa_manage',
            
            // ===== BACKUP & RESTORE =====
            'backup.create',
            'backup.restore',
            'backup.download',
            'backup.schedule',
            
            // ===== INTEGRATIONS =====
            'integrations.view',
            'integrations.create',
            'integrations.edit',
            'integrations.delete',
            'integrations.test',
            
            // ===== SETTINGS =====
            'settings.view',
            'settings.edit',
            'settings.system',
            'settings.email',
            'settings.payment',
            'settings.notifications',
            
            // ===== AUDIT =====
            'audit.view',
            'audit.export',
            'audit.analytics',
            
            // ===== BULK OPERATIONS =====
            'bulk_actions.execute',
            'bulk_actions.schedule',
            'bulk_actions.monitor',
            
            // ===== EXPORT/IMPORT =====
            'export.data',
            'import.data',
            'export.schedule',
            'import.schedule',
            
            // ===== MONITORING =====
            'monitoring.view',
            'monitoring.alerts',
            'monitoring.metrics',
            'monitoring.logs',
            
            // ===== MAINTENANCE =====
            'maintenance.view',
            'maintenance.execute',
            'maintenance.schedule',
            'maintenance.rollback',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('✅ All permissions created successfully!');
    }
}