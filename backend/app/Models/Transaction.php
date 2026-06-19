<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'reference_id',
        'metadata',
        'payment_intent_id',
        'buyer_id',
        'seller_id',
        'listing_id',
        'listing_type',
        'commission',
        'net_amount',
        'payment_reference',
        'notes',
        'refund_reason',
        'refunded_at',
        'completed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'refunded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';

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
     * Get the related listing (polymorphic)
     */
    public function listing()
    {
        if ($this->listing_type === 'product') {
            return $this->belongsTo(Product::class, 'listing_id');
        } elseif ($this->listing_type === 'business') {
            return $this->belongsTo(Business::class, 'listing_id');
        }
        return null;
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Calculate commission based on amount
     */
    public static function calculateCommission($amount, $rate = 0.05)
    {
        return $amount * $rate;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
    }

    /**
     * Process refund
     */
    public function processRefund($reason = null)
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refund_reason' => $reason,
            'refunded_at' => now()
        ]);
    }
}
