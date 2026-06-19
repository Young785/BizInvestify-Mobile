<?php

namespace App\Observers;

use App\Models\Message;
use App\Services\RealtimeService;

class MessageRealtimeObserver
{
    public function __construct(private RealtimeService $realtime)
    {
    }

    public function created(Message $message): void
    {
        $this->realtime->publishMessage($message);
    }
}
