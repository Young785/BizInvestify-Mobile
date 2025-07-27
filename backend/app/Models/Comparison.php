<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'type',
        'items',
        'attributes',
        'last_accessed_at'
    ];

    protected $casts = [
        'items' => 'array',
        'attributes' => 'array',
        'last_accessed_at' => 'datetime'
    ];

    /**
     * Get the user who owns this comparison
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create a comparison session
     */
    public static function getOrCreateSession($userId = null, $sessionId = null, $type = 'product'): self
    {
        $comparison = self::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('type', $type)
            ->first();

        if (!$comparison) {
            $comparison = self::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'type' => $type,
                'items' => [],
                'attributes' => self::getDefaultAttributes($type)
            ]);
        }

        $comparison->update(['last_accessed_at' => now()]);
        return $comparison;
    }

    /**
     * Add an item to comparison
     */
    public function addItem($itemId): bool
    {
        $items = $this->items ?? [];
        
        // Check if item already exists
        if (in_array($itemId, $items)) {
            return false;
        }

        // Limit to 4 items for comparison
        if (count($items) >= 4) {
            return false;
        }

        $items[] = $itemId;
        $this->update(['items' => $items]);
        return true;
    }

    /**
     * Remove an item from comparison
     */
    public function removeItem($itemId): bool
    {
        $items = $this->items ?? [];
        $items = array_filter($items, fn($id) => $id != $itemId);
        
        $this->update(['items' => array_values($items)]);
        return true;
    }

    /**
     * Check if item is in comparison
     */
    public function hasItem($itemId): bool
    {
        $items = $this->items ?? [];
        return in_array($itemId, $items);
    }

    /**
     * Get comparison items with full data
     */
    public function getItemsData(): array
    {
        $items = $this->items ?? [];
        
        if ($this->type === 'product') {
            return Product::whereIn('id', $items)->get()->toArray();
        } else {
            return Business::whereIn('id', $items)->get()->toArray();
        }
    }

    /**
     * Get comparison data for display
     */
    public function getComparisonData(): array
    {
        $items = $this->getItemsData();
        $attributes = $this->attributes ?? self::getDefaultAttributes($this->type);
        
        $comparisonData = [];
        
        foreach ($items as $item) {
            $itemData = [
                'id' => $item['id'],
                'name' => $item['name'] ?? $item['title'],
                'description' => $item['description'],
                'images' => $item['images'] ?? [],
                'logo' => $item['logo'] ?? null,
                'price' => $item['price'] ?? null,
                'valuation' => $item['valuation'] ?? null,
                'category' => $item['category'] ?? null,
                'industry' => $item['industry'] ?? null,
                'status' => $item['status'],
                'created_at' => $item['created_at'],
                'user' => null
            ];

            // Add type-specific attributes
            if ($this->type === 'product') {
                $itemData['condition'] = $item['condition'] ?? null;
                $itemData['tags'] = $item['tags'] ?? [];
            } else {
                $itemData['funding_goal'] = $item['funding_goal'] ?? null;
                $itemData['equity_offered'] = $item['equity_offered'] ?? null;
                $itemData['documents'] = $item['documents'] ?? [];
            }

            // Get user data
            if (isset($item['user_id'])) {
                $user = User::find($item['user_id']);
                if ($user) {
                    $itemData['user'] = [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'rating' => $user->rating ?? 0
                    ];
                }
            }

            // Get review statistics
            if ($this->type === 'product') {
                $itemData['review_stats'] = [
                    'average_rating' => Review::getAverageRating('product', $item['id']),
                    'total_reviews' => Review::getReviewCount('product', $item['id'])
                ];
            } else {
                $itemData['review_stats'] = [
                    'average_rating' => Review::getAverageRating('business', $item['id']),
                    'total_reviews' => Review::getReviewCount('business', $item['id'])
                ];
            }

            $comparisonData[] = $itemData;
        }

        return [
            'items' => $comparisonData,
            'attributes' => $attributes,
            'type' => $this->type
        ];
    }

    /**
     * Get default attributes for comparison
     */
    public static function getDefaultAttributes($type): array
    {
        if ($type === 'product') {
            return [
                'basic' => ['name', 'price', 'category', 'condition', 'description'],
                'seller' => ['seller_name', 'seller_rating', 'seller_location'],
                'reviews' => ['average_rating', 'total_reviews', 'verified_purchases'],
                'details' => ['tags', 'images', 'created_at']
            ];
        } else {
            return [
                'basic' => ['name', 'valuation', 'industry', 'description'],
                'investment' => ['funding_goal', 'equity_offered', 'status'],
                'owner' => ['owner_name', 'owner_rating', 'owner_location'],
                'reviews' => ['average_rating', 'total_reviews', 'verified_investments'],
                'details' => ['documents', 'created_at']
            ];
        }
    }

    /**
     * Clean up old comparison sessions
     */
    public static function cleanupOldSessions($days = 7): int
    {
        return self::where('last_accessed_at', '<', now()->subDays($days))
            ->whereNull('user_id') // Only clean up guest sessions
            ->delete();
    }

    /**
     * Get comparison count for a user
     */
    public static function getUserComparisonCount($userId, $type = null): int
    {
        $query = self::where('user_id', $userId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->count();
    }

    /**
     * Scope for active comparisons
     */
    public function scopeActive($query)
    {
        return $query->where('last_accessed_at', '>=', now()->subDays(30));
    }

    /**
     * Scope for comparisons with items
     */
    public function scopeWithItems($query)
    {
        return $query->whereRaw('JSON_LENGTH(items) > 0');
    }
}
