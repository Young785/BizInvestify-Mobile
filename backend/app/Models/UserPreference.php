<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interests',
        'preferred_categories',
        'preferred_locations',
        'price_range',
        'investment_range',
        'viewed_items',
        'search_history',
        'click_behavior',
        'purchase_history',
        'rating_history',
        'wishlist_items',
        'comparison_history',
        'session_data',
        'last_activity'
    ];

    protected $casts = [
        'interests' => 'array',
        'preferred_categories' => 'array',
        'preferred_locations' => 'array',
        'price_range' => 'array',
        'investment_range' => 'array',
        'viewed_items' => 'array',
        'search_history' => 'array',
        'click_behavior' => 'array',
        'purchase_history' => 'array',
        'rating_history' => 'array',
        'wishlist_items' => 'array',
        'comparison_history' => 'array',
        'session_data' => 'array',
        'last_activity' => 'datetime'
    ];

    /**
     * Get the user who owns these preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update user's last activity
     */
    public function updateActivity(): bool
    {
        return $this->update(['last_activity' => now()]);
    }

    /**
     * Add a viewed item to history
     */
    public function addViewedItem(string $type, int $itemId, array $metadata = []): bool
    {
        $viewedItems = $this->viewed_items ?? [];
        $viewedItems[] = [
            'type' => $type,
            'item_id' => $itemId,
            'viewed_at' => now()->toISOString(),
            'metadata' => $metadata
        ];

        // Keep only last 50 viewed items
        $viewedItems = array_slice($viewedItems, -50);

        return $this->update([
            'viewed_items' => $viewedItems,
            'last_activity' => now()
        ]);
    }

    /**
     * Add a search term to history
     */
    public function addSearchTerm(string $term, array $metadata = []): bool
    {
        $searchHistory = $this->search_history ?? [];
        
        // Check if term already exists
        $existingIndex = null;
        foreach ($searchHistory as $index => $search) {
            if ($search['term'] === $term) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Update existing term
            $searchHistory[$existingIndex]['count']++;
            $searchHistory[$existingIndex]['last_searched'] = now()->toISOString();
            $searchHistory[$existingIndex]['metadata'] = array_merge(
                $searchHistory[$existingIndex]['metadata'] ?? [],
                $metadata
            );
        } else {
            // Add new term
            $searchHistory[] = [
                'term' => $term,
                'count' => 1,
                'first_searched' => now()->toISOString(),
                'last_searched' => now()->toISOString(),
                'metadata' => $metadata
            ];
        }

        // Keep only last 100 search terms
        $searchHistory = array_slice($searchHistory, -100);

        return $this->update([
            'search_history' => $searchHistory,
            'last_activity' => now()
        ]);
    }

    /**
     * Add a purchase/investment to history
     */
    public function addPurchase(string $type, int $itemId, float $amount, array $metadata = []): bool
    {
        $purchaseHistory = $this->purchase_history ?? [];
        $purchaseHistory[] = [
            'type' => $type,
            'item_id' => $itemId,
            'amount' => $amount,
            'purchased_at' => now()->toISOString(),
            'metadata' => $metadata
        ];

        // Keep only last 100 purchases
        $purchaseHistory = array_slice($purchaseHistory, -100);

        return $this->update([
            'purchase_history' => $purchaseHistory,
            'last_activity' => now()
        ]);
    }

    /**
     * Add a rating to history
     */
    public function addRating(string $type, int $itemId, int $rating, array $metadata = []): bool
    {
        $ratingHistory = $this->rating_history ?? [];
        
        // Check if item already rated
        $existingIndex = null;
        foreach ($ratingHistory as $index => $rate) {
            if ($rate['type'] === $type && $rate['item_id'] === $itemId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Update existing rating
            $ratingHistory[$existingIndex]['rating'] = $rating;
            $ratingHistory[$existingIndex]['updated_at'] = now()->toISOString();
            $ratingHistory[$existingIndex]['metadata'] = array_merge(
                $ratingHistory[$existingIndex]['metadata'] ?? [],
                $metadata
            );
        } else {
            // Add new rating
            $ratingHistory[] = [
                'type' => $type,
                'item_id' => $itemId,
                'rating' => $rating,
                'rated_at' => now()->toISOString(),
                'metadata' => $metadata
            ];
        }

        return $this->update([
            'rating_history' => $ratingHistory,
            'last_activity' => now()
        ]);
    }

    /**
     * Update preferred categories
     */
    public function updatePreferredCategories(array $categories): bool
    {
        return $this->update([
            'preferred_categories' => $categories,
            'last_activity' => now()
        ]);
    }

    /**
     * Update preferred locations
     */
    public function updatePreferredLocations(array $locations): bool
    {
        return $this->update([
            'preferred_locations' => $locations,
            'last_activity' => now()
        ]);
    }

    /**
     * Update price range preferences
     */
    public function updatePriceRange(float $min, float $max): bool
    {
        return $this->update([
            'price_range' => ['min' => $min, 'max' => $max],
            'last_activity' => now()
        ]);
    }

    /**
     * Update investment range preferences
     */
    public function updateInvestmentRange(float $min, float $max): bool
    {
        return $this->update([
            'investment_range' => ['min' => $min, 'max' => $max],
            'last_activity' => now()
        ]);
    }

    /**
     * Get user's most viewed categories
     */
    public function getMostViewedCategories(int $limit = 5): array
    {
        $viewedItems = $this->viewed_items ?? [];
        $categoryCounts = [];

        foreach ($viewedItems as $item) {
            $category = $item['metadata']['category'] ?? 'unknown';
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
        }

        arsort($categoryCounts);
        return array_slice($categoryCounts, 0, $limit, true);
    }

    /**
     * Get user's most searched terms
     */
    public function getMostSearchedTerms(int $limit = 10): array
    {
        $searchHistory = $this->search_history ?? [];
        
        // Sort by count (descending)
        usort($searchHistory, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($searchHistory, 0, $limit);
    }

    /**
     * Get user's average rating
     */
    public function getAverageRating(): float
    {
        $ratingHistory = $this->rating_history ?? [];
        
        if (empty($ratingHistory)) {
            return 0.0;
        }

        $totalRating = array_sum(array_column($ratingHistory, 'rating'));
        return round($totalRating / count($ratingHistory), 2);
    }

    /**
     * Get user's total spending
     */
    public function getTotalSpending(): float
    {
        $purchaseHistory = $this->purchase_history ?? [];
        return array_sum(array_column($purchaseHistory, 'amount'));
    }

    /**
     * Get user's activity level (based on last activity)
     */
    public function getActivityLevel(): string
    {
        if (!$this->last_activity) {
            return 'inactive';
        }

        $daysSinceActivity = now()->diffInDays($this->last_activity);

        if ($daysSinceActivity <= 1) {
            return 'very_active';
        } elseif ($daysSinceActivity <= 7) {
            return 'active';
        } elseif ($daysSinceActivity <= 30) {
            return 'moderate';
        } else {
            return 'inactive';
        }
    }
}
