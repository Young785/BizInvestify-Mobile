<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wishlistable_type',
        'wishlistable_id',
        'notes',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    /**
     * Get the user who owns the wishlist item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wishlistable item (product or business)
     */
    public function wishlistable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for public wishlists
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for user's wishlist
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific type (product or business)
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('wishlistable_type', $type);
    }

    /**
     * Check if item is in user's wishlist
     */
    public static function isInWishlist($userId, $itemType, $itemId): bool
    {
        return self::where('user_id', $userId)
            ->where('wishlistable_type', $itemType)
            ->where('wishlistable_id', $itemId)
            ->exists();
    }

    /**
     * Add item to wishlist
     */
    public static function addToWishlist($userId, $itemType, $itemId, $notes = null, $isPublic = false): ?self
    {
        // Check if already in wishlist
        if (self::isInWishlist($userId, $itemType, $itemId)) {
            return null;
        }

        return self::create([
            'user_id' => $userId,
            'wishlistable_type' => $itemType,
            'wishlistable_id' => $itemId,
            'notes' => $notes,
            'is_public' => $isPublic
        ]);
    }

    /**
     * Remove item from wishlist
     */
    public static function removeFromWishlist($userId, $itemType, $itemId): bool
    {
        return self::where('user_id', $userId)
            ->where('wishlistable_type', $itemType)
            ->where('wishlistable_id', $itemId)
            ->delete() > 0;
    }

    /**
     * Get user's wishlist with items
     */
    public static function getUserWishlist($userId, $type = null)
    {
        $query = self::with(['wishlistable', 'user'])
            ->where('user_id', $userId);

        if ($type) {
            $query->ofType($type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get public wishlists
     */
    public static function getPublicWishlists($type = null, $limit = 20)
    {
        $query = self::with(['wishlistable', 'user'])
            ->public();

        if ($type) {
            $query->ofType($type);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
