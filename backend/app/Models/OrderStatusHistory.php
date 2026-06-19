<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = ['status', 'previous_status', 'changed_by'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusAttribute(): ?string
    {
        return $this->to_status;
    }

    public function getPreviousStatusAttribute(): ?string
    {
        return $this->from_status;
    }

    public function getChangedByAttribute(): ?int
    {
        return $this->user_id;
    }
}
