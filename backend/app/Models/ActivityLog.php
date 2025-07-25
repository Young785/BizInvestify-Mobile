<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        'location',
        'metadata',
        'severity',
        'is_admin_action'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_admin_action' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for admin actions
     */
    public function scopeAdminActions($query)
    {
        return $query->where('is_admin_action', true);
    }

    /**
     * Scope for user actions
     */
    public function scopeUserActions($query)
    {
        return $query->where('is_admin_action', false);
    }

    /**
     * Scope for specific activity types
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for high severity activities
     */
    public function scopeHighSeverity($query)
    {
        return $query->where('severity', 'high');
    }

    /**
     * Get activity type label
     */
    public function getActivityTypeLabelAttribute(): string
    {
        return match($this->activity_type) {
            'login' => 'User Login',
            'login_failed' => 'Failed Login Attempt',
            'logout' => 'User Logout',
            'register' => 'User Registration',
            'profile_update' => 'Profile Updated',
            'kyc_submitted' => 'KYC Documents Submitted',
            'kyc_approved' => 'KYC Approved',
            'kyc_rejected' => 'KYC Rejected',
            'listing_created' => 'Listing Created',
            'listing_updated' => 'Listing Updated',
            'listing_deleted' => 'Listing Deleted',
            'investment_made' => 'Investment Made',
            'transaction_completed' => 'Transaction Completed',
            'user_suspended' => 'User Suspended',
            'user_activated' => 'User Activated',
            'user_deleted' => 'User Deleted',
            'admin_login' => 'Admin Login',
            'admin_action' => 'Admin Action',
            'system_event' => 'System Event',
            default => 'Unknown Activity'
        };
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'red',
            'critical' => 'red',
            default => 'gray'
        };
    }

    /**
     * Create activity log entry
     */
    public static function log($userId, $activityType, $description, $metadata = [], $severity = 'medium', $isAdminAction = false): self
    {
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location' => self::getLocationFromIP(request()->ip()),
            'metadata' => $metadata,
            'severity' => $severity,
            'is_admin_action' => $isAdminAction,
        ]);
    }

    /**
     * Get location from IP address
     */
    private static function getLocationFromIP($ip): ?string
    {
        // In production, you might want to use a service like MaxMind or IP2Location
        // For now, we'll return null
        return null;
    }

    /**
     * Get recent activities for a user
     */
    public static function getRecentActivities($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get admin activities
     */
    public static function getAdminActivities($limit = 50)
    {
        return self::where('is_admin_action', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get suspicious activities
     */
    public static function getSuspiciousActivities($limit = 50)
    {
        return self::whereIn('activity_type', ['login_failed', 'suspicious_activity'])
            ->orWhere('severity', 'high')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
} 