<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\RealtimeService;

class NotificationRealtimeObserver
{
    public function __construct(private RealtimeService $realtime)
    {
    }

    public function created(Notification $notification): void
    {
        $this->realtime->publishNotification($notification);
    }
}
