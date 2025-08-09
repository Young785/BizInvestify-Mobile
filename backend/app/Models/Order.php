<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_email',
        'product_id',
        'product_name',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_intent_id',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['formatted_amount'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Y') . '-' . str_pad(
                    Order::whereYear('created_at', date('Y'))->count() + 1, 
                    6, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Get the user that owns the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the order
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted amount attribute
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency ?? 'USD');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for user orders
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('order_number', 'like', "%{$term}%")
              ->orWhere('customer_name', 'like', "%{$term}%")
              ->orWhere('customer_email', 'like', "%{$term}%")
              ->orWhere('product_name', 'like', "%{$term}%");
        });
    }
} 