<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'metadata',
        'last_message_at',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the participants in this conversation
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['role', 'joined_at', 'left_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get the messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in this conversation
     */
    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Check if a user is a participant in this conversation
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a participant to this conversation
     */
    public function addParticipant(User $user, string $role = 'participant'): void
    {
        $this->participants()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Remove a participant from this conversation
     */
    public function removeParticipant(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'left_at' => now(),
            'is_active' => false
        ]);
    }

    /**
     * Get unread message count for a specific user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark messages as read for a specific user
     */
    public function markAsRead(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Create a direct conversation between two users
     */
    public static function createDirect(User $user1, User $user2): self
    {
        $conversation = self::create([
            'type' => 'direct',
            'title' => null
        ]);

        $conversation->addParticipant($user1);
        $conversation->addParticipant($user2);

        return $conversation;
    }

    /**
     * Create a group conversation
     */
    public static function createGroup(string $title, array $participants): self
    {
        $conversation = self::create([
            'type' => 'group',
            'title' => $title
        ]);

        foreach ($participants as $participant) {
            $conversation->addParticipant($participant);
        }

        return $conversation;
    }

    /**
     * Get conversations for a specific user
     */
    public static function forUser(User $user): \Illuminate\Database\Eloquent\Builder
    {
        return self::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('is_active', true);
        })->where('is_active', true);
    }

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for direct conversations
     */
    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    /**
     * Scope for group conversations
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }
}
