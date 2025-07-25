<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Log user login activity
     */
    public static function logLogin(User $user, bool $isAdmin = false): void
    {
        $metadata = [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location' => self::getLocationFromIP(request()->ip())
        ];

        ActivityLog::log(
            $user->id,
            $isAdmin ? 'admin_login' : 'login',
            $user->first_name . ' ' . $user->last_name . ' logged in from ' . request()->ip(),
            $metadata,
            'medium',
            $isAdmin
        );

        // Check for suspicious login (different IP, location, etc.)
        self::checkSuspiciousLogin($user, $metadata);
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        $metadata = [
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => $reason
        ];

        ActivityLog::log(
            null, // No user ID for failed login
            'login_failed',
            'Failed login attempt for email: ' . $email . ' from ' . request()->ip(),
            $metadata,
            'high'
        );

        // Check for brute force attempts
        self::checkBruteForceAttempt($email);
    }

    /**
     * Log user registration
     */
    public static function logRegistration(User $user): void
    {
        ActivityLog::log(
            $user->id,
            'register',
            $user->first_name . ' ' . $user->last_name . ' registered as ' . $user->role,
            ['role' => $user->role],
            'medium'
        );

        // Send welcome notification
        Notification::createNotification(
            $user->id,
            'success',
            'Welcome to BizInvestify!',
            'Thank you for joining our platform. Please complete your profile and KYC verification to get started.',
            ['welcome' => true],
            'medium'
        );
    }

    /**
     * Log KYC submission
     */
    public static function logKYCSubmission(User $user): void
    {
        ActivityLog::log(
            $user->id,
            'kyc_submitted',
            $user->first_name . ' ' . $user->last_name . ' submitted KYC documents',
            ['user_id' => $user->id],
            'medium'
        );

        // Notify admins about new KYC submission
        self::notifyAdminsKYCSubmission($user);
    }

    /**
     * Log KYC approval
     */
    public static function logKYCApproval(User $user, User $admin): void
    {
        ActivityLog::log(
            $admin->id,
            'kyc_approved',
            'KYC approved for ' . $user->first_name . ' ' . $user->last_name . ' by ' . $admin->first_name . ' ' . $admin->last_name,
            ['user_id' => $user->id, 'admin_id' => $admin->id],
            'medium',
            true
        );

        // Notify user about KYC approval
        Notification::createNotification(
            $user->id,
            'kyc_approved',
            'KYC Verification Approved',
            'Congratulations! Your KYC verification has been approved. You can now access all platform features.',
            ['kyc_status' => 'approved'],
            'high'
        );
    }

    /**
     * Log KYC rejection
     */
    public static function logKYCRejection(User $user, User $admin, string $reason): void
    {
        ActivityLog::log(
            $admin->id,
            'kyc_rejected',
            'KYC rejected for ' . $user->first_name . ' ' . $user->last_name . ' by ' . $admin->first_name . ' ' . $admin->last_name,
            ['user_id' => $user->id, 'admin_id' => $admin->id, 'reason' => $reason],
            'medium',
            true
        );

        // Notify user about KYC rejection
        Notification::createNotification(
            $user->id,
            'kyc_rejected',
            'KYC Verification Rejected',
            'Your KYC verification was rejected. Reason: ' . $reason . '. Please review and resubmit your documents.',
            ['kyc_status' => 'rejected', 'reason' => $reason],
            'high'
        );
    }

    /**
     * Log listing creation
     */
    public static function logListingCreation(User $user, string $listingType, string $listingTitle): void
    {
        ActivityLog::log(
            $user->id,
            'listing_created',
            $user->first_name . ' ' . $user->last_name . ' created a new ' . $listingType . ' listing: ' . $listingTitle,
            ['listing_type' => $listingType, 'listing_title' => $listingTitle],
            'medium'
        );

        // Notify admins about new listing
        self::notifyAdminsNewListing($user, $listingType, $listingTitle);
    }

    /**
     * Log investment made
     */
    public static function logInvestment(User $user, float $amount, string $businessName): void
    {
        ActivityLog::log(
            $user->id,
            'investment_made',
            $user->first_name . ' ' . $user->last_name . ' made an investment of $' . number_format($amount, 2) . ' in ' . $businessName,
            ['amount' => $amount, 'business_name' => $businessName],
            'medium'
        );

        // Notify business owner about investment
        self::notifyBusinessOwnerInvestment($user, $amount, $businessName);
    }

    /**
     * Log transaction completion
     */
    public static function logTransaction(User $user, float $amount, string $transactionType): void
    {
        ActivityLog::log(
            $user->id,
            'transaction_completed',
            $user->first_name . ' ' . $user->last_name . ' completed a ' . $transactionType . ' transaction of $' . number_format($amount, 2),
            ['amount' => $amount, 'transaction_type' => $transactionType],
            'medium'
        );

        // Send payment confirmation notification
        Notification::createNotification(
            $user->id,
            'payment_received',
            'Payment Confirmation',
            'Your payment of $' . number_format($amount, 2) . ' has been processed successfully.',
            ['amount' => $amount, 'transaction_type' => $transactionType],
            'medium'
        );
    }

    /**
     * Log admin actions
     */
    public static function logAdminAction(User $admin, string $action, string $description, array $metadata = []): void
    {
        ActivityLog::log(
            $admin->id,
            'admin_action',
            $admin->first_name . ' ' . $admin->last_name . ' performed: ' . $action,
            array_merge($metadata, ['action' => $action]),
            'medium',
            true
        );
    }

    /**
     * Log user suspension
     */
    public static function logUserSuspension(User $user, User $admin, string $reason): void
    {
        ActivityLog::log(
            $admin->id,
            'user_suspended',
            $admin->first_name . ' ' . $admin->last_name . ' suspended user ' . $user->first_name . ' ' . $user->last_name,
            ['user_id' => $user->id, 'admin_id' => $admin->id, 'reason' => $reason],
            'high',
            true
        );

        // Notify user about suspension
        Notification::createNotification(
            $user->id,
            'error',
            'Account Suspended',
            'Your account has been suspended. Reason: ' . $reason . '. Please contact support for assistance.',
            ['suspension_reason' => $reason],
            'urgent'
        );
    }

    /**
     * Log user activation
     */
    public static function logUserActivation(User $user, User $admin): void
    {
        ActivityLog::log(
            $admin->id,
            'user_activated',
            $admin->first_name . ' ' . $admin->last_name . ' activated user ' . $user->first_name . ' ' . $user->last_name,
            ['user_id' => $user->id, 'admin_id' => $admin->id],
            'medium',
            true
        );

        // Notify user about activation
        Notification::createNotification(
            $user->id,
            'success',
            'Account Activated',
            'Your account has been reactivated. You can now access all platform features.',
            ['activation' => true],
            'medium'
        );
    }

    /**
     * Check for suspicious login
     */
    private static function checkSuspiciousLogin(User $user, array $metadata): void
    {
        // Check if login is from a new IP address
        $recentLogins = ActivityLog::where('user_id', $user->id)
            ->where('activity_type', 'login')
            ->where('created_at', '>', now()->subDays(30))
            ->get();

        $knownIPs = $recentLogins->pluck('metadata.ip_address')->unique()->toArray();
        
        if (!in_array($metadata['ip_address'], $knownIPs)) {
            // Log suspicious activity
            ActivityLog::log(
                $user->id,
                'suspicious_activity',
                'Login from new IP address: ' . $metadata['ip_address'],
                $metadata,
                'high'
            );

            // Send security alert notification
            Notification::createNotification(
                $user->id,
                'security_alert',
                'New Login Detected',
                'We detected a login from a new IP address: ' . $metadata['ip_address'] . '. If this wasn\'t you, please contact support immediately.',
                $metadata,
                'high'
            );
        }
    }

    /**
     * Check for brute force attempts
     */
    private static function checkBruteForceAttempt(string $email): void
    {
        $recentFailures = ActivityLog::where('activity_type', 'login_failed')
            ->where('metadata->email', $email)
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();

        if ($recentFailures >= 5) {
            // Log potential brute force attempt
            ActivityLog::log(
                null,
                'suspicious_activity',
                'Potential brute force attempt for email: ' . $email,
                ['email' => $email, 'failed_attempts' => $recentFailures],
                'critical'
            );

            // Notify admins about potential security threat
            self::notifyAdminsSecurityThreat($email, $recentFailures);
        }
    }

    /**
     * Notify admins about KYC submission
     */
    private static function notifyAdminsKYCSubmission(User $user): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'info',
                'New KYC Submission',
                $user->first_name . ' ' . $user->last_name . ' has submitted KYC documents for review.',
                ['user_id' => $user->id, 'user_name' => $user->first_name . ' ' . $user->last_name],
                'medium'
            );
        }
    }

    /**
     * Notify admins about new listing
     */
    private static function notifyAdminsNewListing(User $user, string $listingType, string $listingTitle): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'info',
                'New ' . ucfirst($listingType) . ' Listing',
                $user->first_name . ' ' . $user->last_name . ' created a new ' . $listingType . ' listing: ' . $listingTitle,
                ['user_id' => $user->id, 'listing_type' => $listingType, 'listing_title' => $listingTitle],
                'medium'
            );
        }
    }

    /**
     * Notify business owner about investment
     */
    private static function notifyBusinessOwnerInvestment(User $investor, float $amount, string $businessName): void
    {
        // This would need to be implemented based on your business model
        // For now, we'll just log it
        Log::info('Investment notification would be sent to business owner', [
            'investor' => $investor->id,
            'amount' => $amount,
            'business_name' => $businessName
        ]);
    }

    /**
     * Notify admins about security threat
     */
    private static function notifyAdminsSecurityThreat(string $email, int $attempts): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        
        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'security_alert',
                'Security Alert: Brute Force Attempt',
                'Potential brute force attempt detected for email: ' . $email . ' (' . $attempts . ' failed attempts)',
                ['email' => $email, 'attempts' => $attempts],
                'urgent'
            );
        }
    }

    /**
     * Get location from IP address
     */
    private static function getLocationFromIP(string $ip): ?string
    {
        // In production, you might want to use a service like MaxMind or IP2Location
        // For now, we'll return null
        return null;
    }
} 