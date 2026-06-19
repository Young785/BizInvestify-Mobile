<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'message',
        'priority',
        'category',
        'source',
        'status',
        'assigned_to',
        'resolved_at',
        'resolved_by'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function threadMessages()
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('created_at');
    }

    public function isLiveChat(): bool
    {
        return $this->source === 'live_chat';
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Accessors
    public function getPriorityLabelAttribute()
    {
        return ucfirst($this->priority);
    }

    public function getStatusLabelAttribute()
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }

    public function getCategoryLabelAttribute()
    {
        return ucfirst($this->category);
    }
} 