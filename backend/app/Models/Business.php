<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'description',
        'industry',
        'valuation',
        'funding_goal',
        'funded_amount',
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
        'funded_amount' => 'decimal:2',
        'equity_offered' => 'decimal:2',
        'revenue' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'employees_count' => 'integer',
        'founded_year' => 'integer',
        'views_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            if (empty($business->slug)) {
                $business->slug = static::generateUniqueSlug($business->name);
            }
        });

        static::updating(function (Business $business) {
            if ($business->isDirty('name') && ! $business->isDirty('slug')) {
                $business->slug = static::generateUniqueSlug($business->name, $business->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name) ?: 'business';
        $original = $slug;
        $count = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    public static function findBySlugOrId(string $identifier): ?self
    {
        return static::query()
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first();
    }

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
     * Get the transactions for this business.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'listing_id')->where('listing_type', 'business');
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
     * Remaining amount this business can still raise.
     */
    public function remainingFunding(): float
    {
        $funded = (float) ($this->funded_amount ?? 0);
        if ($funded <= 0) {
            $funded = (float) $this->total_invested;
        }

        return max(0, (float) $this->funding_goal - $funded);
    }

    /**
     * Increment the views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get the wishlist entries for this business.
     */
    public function wishlists(): MorphMany
    {
        return $this->morphMany(Wishlist::class, 'wishlistable');
    }

    /**
     * Get the reviews for this business.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Get the approved reviews for this business.
     */
    public function approvedReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->approved();
    }

    /**
     * Get the average rating for this business.
     */
    public function getAverageRatingAttribute(): float
    {
        return Review::getAverageRating('business', $this->id);
    }

    /**
     * Get the review count for this business.
     */
    public function getReviewCountAttribute(): int
    {
        return Review::getReviewCount('business', $this->id);
    }

    /**
     * Get the verified investment count for this business.
     */
    public function getVerifiedInvestmentCountAttribute(): int
    {
        return Review::getVerifiedPurchaseCount('business', $this->id);
    }
}
