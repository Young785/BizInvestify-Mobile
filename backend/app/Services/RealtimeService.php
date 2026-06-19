<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Notification;
use App\Models\RealtimeEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class RealtimeService
{
    public function publish(int $userId, string $channel, string $eventType, array $payload): RealtimeEvent
    {
        return RealtimeEvent::create([
            'user_id' => $userId,
            'channel' => $channel,
            'event_type' => $eventType,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public function poll(User $user, int $sinceId = 0, ?array $channels = null): array
    {
        $query = RealtimeEvent::query()
            ->where('user_id', $user->id)
            ->where('id', '>', max(0, $sinceId))
            ->orderBy('id');

        if ($channels) {
            $query->whereIn('channel', $channels);
        }

        $events = $query->limit(100)->get();
        $cursor = $events->last()?->id ?? $sinceId;

        return [
            'cursor' => $cursor,
            'events' => $events->map(fn (RealtimeEvent $event) => $this->formatEvent($event))->values()->all(),
        ];
    }

    public function publishNotification(Notification $notification): void
    {
        $formatted = app(NotificationService::class)->format($notification);

        $this->publish(
            (int) $notification->user_id,
            'notifications',
            'notification.new',
            [
                'id' => (string) $notification->id,
                'type' => $formatted['ui_type'],
                'category' => $formatted['category'],
                'title' => $notification->title,
                'message' => $notification->message,
                'timestamp' => $notification->created_at?->toIso8601String() ?? now()->toIso8601String(),
                'read' => (bool) $notification->is_read,
                'action_url' => $formatted['action_url'],
            ]
        );
    }

    public function publishMessage(Message $message): void
    {
        $message->loadMissing(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name']);

        $payload = [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message' => $message->message,
            'listing_id' => $message->listing_id,
            'listing_type' => $message->listing_type,
            'is_read' => (bool) $message->is_read,
            'created_at' => $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'first_name' => $message->sender->first_name,
                'last_name' => $message->sender->last_name,
            ] : null,
        ];

        $this->publish((int) $message->receiver_id, 'messages', 'message.new', $payload);
        $this->publish((int) $message->sender_id, 'messages', 'message.sent', $payload);
    }

    public function pruneOldEvents(int $days = 7): int
    {
        return RealtimeEvent::where('created_at', '<', now()->subDays($days))->delete();
    }

    private function formatEvent(RealtimeEvent $event): array
    {
        return [
            'id' => $event->id,
            'channel' => $event->channel,
            'event_type' => $event->event_type,
            'payload' => $event->payload,
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }

    private function mapNotificationUiType(string $type): string
    {
        return match ($type) {
            'success', 'kyc_approved', 'payment_received' => 'success',
            'warning', 'kyc_rejected', 'security_alert' => 'warning',
            'error' => 'error',
            default => 'info',
        };
    }
}
