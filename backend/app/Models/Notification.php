<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\NotificationFactory::new();
    }

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
        'priority',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for high priority notifications
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope for active notifications (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Get notification type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'info' => 'Information',
            'success' => 'Success',
            'warning' => 'Warning',
            'error' => 'Error',
            'kyc_approved' => 'KYC Approved',
            'kyc_rejected' => 'KYC Rejected',
            'investment_received' => 'Investment Received',
            'listing_approved' => 'Listing Approved',
            'listing_rejected' => 'Listing Rejected',
            'payment_received' => 'Payment Received',
            'system_maintenance' => 'System Maintenance',
            'security_alert' => 'Security Alert',
            default => 'Notification'
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'blue',
            'medium' => 'yellow',
            'high' => 'red',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    /**
     * Create notification
     */
    public static function createNotification($userId, $type, $title, $message, $data = [], $priority = 'medium', $expiresAt = null): ?self
    {
        $user = User::find($userId);
        $service = app(\App\Services\NotificationService::class);

        if ($user && ! $service->shouldDeliverInApp($user, $type)) {
            return null;
        }

        if (empty($data['action_url'])) {
            $data['action_url'] = $service->defaultActionUrl($type, $data);
        }

        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'expires_at' => $expiresAt,
            'is_read' => false,
        ]);
    }

    /**
     * Create system-wide notification
     */
    public static function createSystemNotification($type, $title, $message, $data = [], $priority = 'medium', $expiresAt = null): void
    {
        $users = User::where('is_verified', true)->get();
        
        foreach ($users as $user) {
            self::createNotification(
                $user->id,
                $type,
                $title,
                $message,
                $data,
                $priority,
                $expiresAt
            );
        }
    }

    /**
     * Create notification for specific user role
     */
    public static function createRoleNotification($role, $type, $title, $message, $data = [], $priority = 'medium', $expiresAt = null): void
    {
        $users = User::where('role', $role)
            ->where('is_verified', true)
            ->get();
        
        foreach ($users as $user) {
            self::createNotification(
                $user->id,
                $type,
                $title,
                $message,
                $data,
                $priority,
                $expiresAt
            );
        }
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount($userId): int
    {
        return self::where('user_id', $userId)
            ->unread()
            ->active()
            ->count();
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsRead($userId): int
    {
        return self::where('user_id', $userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }
} 