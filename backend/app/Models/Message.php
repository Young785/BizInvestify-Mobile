<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'is_read',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to get messages for a specific conversation.
     */
    public function scopeForConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Scope a query to get messages from a specific sender.
     */
    public function scopeFromSender($query, $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Scope a query to get messages of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Check if message is from a specific user.
     */
    public function isFrom(User $user): bool
    {
        return $this->sender_id === $user->id;
    }

    /**
     * Get formatted content based on message type.
     */
    public function getFormattedContent(): string
    {
        switch ($this->type) {
            case 'text':
                return $this->content;
            case 'image':
                return '[Image]';
            case 'file':
                return '[File]';
            case 'system':
                return $this->content;
            default:
                return $this->content;
        }
    }

    /**
     * Get message preview (truncated content).
     */
    public function getPreview(int $length = 50): string
    {
        $content = $this->getFormattedContent();
        return strlen($content) > $length ? substr($content, 0, $length) . '...' : $content;
    }
}
