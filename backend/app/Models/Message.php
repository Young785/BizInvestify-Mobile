<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'listing_id',
        'listing_type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeConversation($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where(function ($inner) use ($userId1, $userId2) {
                $inner->where('sender_id', $userId1)->where('receiver_id', $userId2);
            })->orWhere(function ($inner) use ($userId1, $userId2) {
                $inner->where('sender_id', $userId2)->where('receiver_id', $userId1);
            });
        });
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function isFrom(User $user): bool
    {
        return (int) $this->sender_id === (int) $user->id;
    }

    public function getPreview(int $length = 50): string
    {
        $content = $this->message ?? '';
        return strlen($content) > $length ? substr($content, 0, $length) . '...' : $content;
    }
}
