<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'title',
        'content',
        'images',
        'is_verified_purchase',
        'is_helpful',
        'helpful_count',
        'is_approved',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'rating' => 'integer',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_helpful' => 'boolean',
        'helpful_count' => 'integer',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the user who wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item being reviewed (product or business)
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin who approved the review
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for verified purchases
     */
    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope for reviews by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope for reviews with images
     */
    public function scopeWithImages($query)
    {
        return $query->whereNotNull('images');
    }

    /**
     * Scope for helpful reviews
     */
    public function scopeHelpful($query)
    {
        return $query->where('helpful_count', '>', 0)->orderBy('helpful_count', 'desc');
    }

    /**
     * Scope for recent reviews
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if user can review this item
     */
    public static function canReview($userId, $itemType, $itemId): bool
    {
        // Check if user already reviewed this item
        $existingReview = self::where('user_id', $userId)
            ->where('reviewable_type', $itemType)
            ->where('reviewable_id', $itemId)
            ->exists();

        if ($existingReview) {
            return false;
        }

        // Check if user has purchased/invested in the item
        if ($itemType === 'product') {
            $hasPurchased = Transaction::where('user_id', $userId)
                ->where('type', 'purchase')
                ->where('reference_id', $itemId)
                ->where('status', 'completed')
                ->exists();
        } else {
            $hasInvested = Investment::where('investor_id', $userId)
                ->where('business_id', $itemId)
                ->where('status', 'completed')
                ->exists();
        }

        return $itemType === 'product' ? $hasPurchased : $hasInvested;
    }

    /**
     * Mark review as helpful
     */
    public function markAsHelpful(): bool
    {
        $this->increment('helpful_count');
        $this->is_helpful = true;
        return $this->save();
    }

    /**
     * Unmark review as helpful
     */
    public function unmarkAsHelpful(): bool
    {
        $this->decrement('helpful_count');
        if ($this->helpful_count <= 0) {
            $this->is_helpful = false;
        }
        return $this->save();
    }

    /**
     * Approve review
     */
    public function approve($adminId): bool
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $adminId
        ]);
        return true;
    }

    /**
     * Reject review
     */
    public function reject($adminId): bool
    {
        $this->update([
            'is_approved' => false,
            'approved_at' => now(),
            'approved_by' => $adminId
        ]);
        return true;
    }

    /**
     * Get average rating for an item
     */
    public static function getAverageRating($itemType, $itemId): float
    {
        return self::where('reviewable_type', $itemType)
            ->where('reviewable_id', $itemId)
            ->approved()
            ->avg('rating') ?? 0;
    }

    /**
     * Get rating distribution for an item
     */
    public static function getRatingDistribution($itemType, $itemId): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = self::where('reviewable_type', $itemType)
                ->where('reviewable_id', $itemId)
                ->where('rating', $i)
                ->approved()
                ->count();
            $distribution[$i] = $count;
        }
        return $distribution;
    }

    /**
     * Get total review count for an item
     */
    public static function getReviewCount($itemType, $itemId): int
    {
        return self::where('reviewable_type', $itemType)
            ->where('reviewable_id', $itemId)
            ->approved()
            ->count();
    }

    /**
     * Get verified purchase count for an item
     */
    public static function getVerifiedPurchaseCount($itemType, $itemId): int
    {
        return self::where('reviewable_type', $itemType)
            ->where('reviewable_id', $itemId)
            ->approved()
            ->verifiedPurchase()
            ->count();
    }
}
