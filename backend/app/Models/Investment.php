<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'business_id',
        'amount',
        'equity_percentage',
        'status',
        'message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'equity_percentage' => 'decimal:2',
    ];

    /**
     * Get the investor that made the investment.
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    /**
     * Get the business that received the investment.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope a query to only include pending investments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed investments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include investments by a specific investor.
     */
    public function scopeByInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    /**
     * Scope a query to only include investments for a specific business.
     */
    public function scopeByBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
