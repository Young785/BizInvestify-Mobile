<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'price',
        'currency',
        'category',
        'tags',
        'images',
        'status',
        'inventory_count',
        'views_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'inventory_count' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Get the seller that owns the product.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the transactions for this product.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'listing_id')->where('listing_type', 'product');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include products by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Increment the views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get the wishlist entries for this product.
     */
    public function wishlists(): MorphMany
    {
        return $this->morphMany(Wishlist::class, 'wishlistable');
    }

    /**
     * Get the reviews for this product.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Get the approved reviews for this product.
     */
    public function approvedReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->approved();
    }

    /**
     * Get the average rating for this product.
     */
    public function getAverageRatingAttribute(): float
    {
        return Review::getAverageRating('product', $this->id);
    }

    /**
     * Get the review count for this product.
     */
    public function getReviewCountAttribute(): int
    {
        return Review::getReviewCount('product', $this->id);
    }

    /**
     * Get the verified purchase count for this product.
     */
    public function getVerifiedPurchaseCountAttribute(): int
    {
        return Review::getVerifiedPurchaseCount('product', $this->id);
    }
}
