<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'listing_id',
        'listing_type',
        'amount',
        'commission_amount',
        'payment_method',
        'status',
        'stripe_payment_intent_id',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payment_details' => 'array',
    ];

    /**
     * Get the buyer of the transaction.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller of the transaction.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include transactions by buyer.
     */
    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Scope a query to only include transactions by seller.
     */
    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Scope a query to get transactions for a specific listing.
     */
    public function scopeForListing($query, $listingId, $listingType)
    {
        return $query->where('listing_id', $listingId)
                    ->where('listing_type', $listingType);
    }

    /**
     * Get the net amount (amount minus commission).
     */
    public function getNetAmountAttribute()
    {
        return $this->amount - $this->commission_amount;
    }
}
