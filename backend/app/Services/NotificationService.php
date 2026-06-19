<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function defaultPreferences(): array
    {
        return [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'marketing_emails' => false,
            'security_alerts' => true,
            'kyc_updates' => true,
            'transaction_alerts' => true,
            'investment_updates' => true,
            'listing_updates' => true,
            'message_notifications' => true,
            'frequency' => 'immediate',
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ];
    }

    public function preferencesForUser(User $user): array
    {
        $stored = $user->notification_preferences;

        if (! is_array($stored)) {
            return $this->defaultPreferences();
        }

        return array_merge($this->defaultPreferences(), $stored);
    }

    public function updatePreferences(User $user, array $input): array
    {
        $allowed = array_keys($this->defaultPreferences());
        $filtered = array_intersect_key($input, array_flip($allowed));
        $merged = array_merge($this->preferencesForUser($user), $filtered);

        $user->update(['notification_preferences' => $merged]);

        return $merged;
    }

    public function shouldDeliverInApp(User $user, string $type): bool
    {
        $prefs = $this->preferencesForUser($user);

        if (! ($prefs['push_notifications'] ?? true)) {
            return false;
        }

        return $this->categoryEnabled($prefs, $type);
    }

    public function shouldSendEmail(User $user, string $type): bool
    {
        $prefs = $this->preferencesForUser($user);

        if (! ($prefs['email_notifications'] ?? true)) {
            return false;
        }

        return $this->categoryEnabled($prefs, $type);
    }

    private function categoryEnabled(array $prefs, string $type): bool
    {
        return match ($this->mapCategory($type)) {
            'security' => (bool) ($prefs['security_alerts'] ?? true),
            'kyc' => (bool) ($prefs['kyc_updates'] ?? true),
            'transaction' => (bool) ($prefs['transaction_alerts'] ?? true),
            'investment' => (bool) ($prefs['investment_updates'] ?? true),
            'marketplace' => (bool) ($prefs['listing_updates'] ?? true),
            'message' => (bool) ($prefs['message_notifications'] ?? true),
            default => true,
        };
    }

    public function format(Notification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $type = (string) $notification->type;

        return [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'type' => $type,
            'ui_type' => $this->mapUiType($type),
            'category' => $this->mapCategory($type),
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $data,
            'is_read' => (bool) $notification->is_read,
            'read_at' => $notification->read_at?->toIso8601String(),
            'priority' => $notification->priority,
            'expires_at' => $notification->expires_at?->toIso8601String(),
            'action_url' => $data['action_url'] ?? $this->defaultActionUrl($type, $data),
            'created_at' => $notification->created_at?->toIso8601String(),
            'updated_at' => $notification->updated_at?->toIso8601String(),
        ];
    }

    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'medium',
        ?\DateTimeInterface $expiresAt = null
    ): ?Notification {
        $user = User::find($userId);
        if (! $user || ! $this->shouldDeliverInApp($user, $type)) {
            return null;
        }

        if (empty($data['action_url'])) {
            $data['action_url'] = $this->defaultActionUrl($type, $data);
        }

        return Notification::create([
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

    public function notifyWithEmail(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'medium',
        ?string $emailSubject = null
    ): ?Notification {
        $notification = $this->create($user->id, $type, $title, $message, $data, $priority);

        if ($this->shouldSendEmail($user, $type)) {
            $this->sendTransactionEmail($user, $emailSubject ?? $title, $title, $message, $data);
        }

        return $notification;
    }

    public function sendTransactionEmail(
        User $user,
        string $subject,
        string $headline,
        string $body,
        array $data = []
    ): void {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $actionPath = $data['action_url'] ?? '/dashboard/notifications';
        $actionUrl = str_starts_with($actionPath, 'http')
            ? $actionPath
            : $frontendUrl.$actionPath;

        try {
            Mail::send('emails.transaction-alert', [
                'user' => $user,
                'headline' => $headline,
                'body' => $body,
                'details' => $data['email_details'] ?? [],
                'action_url' => $actionUrl,
                'action_label' => $data['action_label'] ?? 'View Details',
            ], function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Failed to send transaction email', [
                'user_id' => $user->id,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function mapUiType(string $type): string
    {
        return match ($type) {
            'success', 'kyc_approved', 'payment_received', 'listing_approved', 'subscription_activated' => 'success',
            'warning', 'kyc_rejected', 'security_alert', 'system_maintenance' => 'warning',
            'error' => 'error',
            default => 'info',
        };
    }

    public function mapCategory(string $type): string
    {
        return match ($type) {
            'message' => 'message',
            'kyc_approved', 'kyc_rejected' => 'kyc',
            'payment_received', 'transaction', 'refund', 'order_update' => 'transaction',
            'investment_received' => 'investment',
            'subscription_activated' => 'system',
            'listing_approved', 'listing_rejected' => 'marketplace',
            'security_alert' => 'security',
            default => 'system',
        };
    }

    public function defaultActionUrl(string $type, array $data = []): ?string
    {
        if (! empty($data['action_url'])) {
            return $data['action_url'];
        }

        return match ($type) {
            'message' => '/dashboard/messages',
            'kyc_approved', 'kyc_rejected' => '/dashboard/profile/kyc',
            'payment_received', 'transaction', 'refund' => '/dashboard/transactions',
            'order_update' => '/dashboard/orders',
            'investment_received' => '/dashboard/investments',
            'subscription_activated' => '/dashboard/settings',
            'listing_approved', 'listing_rejected' => '/dashboard/featured-listings',
            'security_alert' => '/dashboard/profile/security',
            default => '/dashboard/notifications',
        };
    }

    public static function logRegistration(User $user): void
    {
        ActivityLog::log(
            $user->id,
            'register',
            'User registered on the platform',
            ['email' => $user->email, 'role' => $user->role],
            'low'
        );

        Notification::createNotification(
            $user->id,
            'info',
            'Welcome to BizInvestify',
            'Complete your profile and verification to get started.',
            ['action_url' => '/dashboard/profile'],
            'medium'
        );
    }

    public static function logFailedLogin(?string $email): void
    {
        if (! $email) {
            return;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return;
        }

        ActivityLog::log(
            $user->id,
            'login_failed',
            'Failed login attempt for your account',
            ['email' => $email],
            'high'
        );

        app(self::class)->create(
            $user->id,
            'security_alert',
            'Failed login attempt',
            'We detected a failed login attempt on your account.',
            ['action_url' => '/dashboard/profile/security'],
            'high'
        );
    }

    public static function logLogin(User $user, bool $isAdmin = false): void
    {
        ActivityLog::log(
            $user->id,
            $isAdmin ? 'admin_login' : 'login',
            $isAdmin ? 'Admin logged in' : 'User logged in',
            ['email' => $user->email],
            'low',
            $isAdmin
        );
    }
}
