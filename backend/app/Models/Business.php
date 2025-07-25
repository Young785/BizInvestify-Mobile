<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'name',
        'description',
        'industry',
        'valuation',
        'funding_goal',
        'equity_offered',
        'pitch_deck_url',
        'business_plan',
        'revenue',
        'profit_margin',
        'employees_count',
        'founded_year',
        'location',
        'images',
        'status',
        'views_count',
    ];

    protected $casts = [
        'images' => 'array',
        'valuation' => 'decimal:2',
        'funding_goal' => 'decimal:2',
        'equity_offered' => 'decimal:2',
        'revenue' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'employees_count' => 'integer',
        'founded_year' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Get the seller that owns the business.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the investments for this business.
     */
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Scope a query to only include active businesses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include businesses by industry.
     */
    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope a query to filter by funding goal range.
     */
    public function scopeFundingRange($query, $min, $max)
    {
        return $query->whereBetween('funding_goal', [$min, $max]);
    }

    /**
     * Scope a query to filter by valuation range.
     */
    public function scopeValuationRange($query, $min, $max)
    {
        return $query->whereBetween('valuation', [$min, $max]);
    }

    /**
     * Get the total amount invested in this business.
     */
    public function getTotalInvestedAttribute()
    {
        return $this->investments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get the funding progress percentage.
     */
    public function getFundingProgressAttribute()
    {
        if ($this->funding_goal <= 0) {
            return 0;
        }
        
        return min(100, ($this->total_invested / $this->funding_goal) * 100);
    }

    /**
     * Increment the views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
