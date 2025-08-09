<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Business;
use App\Models\Recommendation;
use App\Models\UserPreference;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    /**
     * Generate personalized recommendations for a user
     */
    public function generateRecommendations(User $user, string $type = 'product', int $limit = 10): array
    {
        $recommendations = [];

        // Get or create user preferences
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);

        // Generate recommendations using different algorithms
        $collaborativeRecs = $this->getCollaborativeRecommendations($user, $type, $limit);
        $contentBasedRecs = $this->getContentBasedRecommendations($user, $type, $limit);
        $trendingRecs = $this->getTrendingRecommendations($type, $limit);
        $hybridRecs = $this->getHybridRecommendations($user, $type, $limit);

        // Combine and rank recommendations
        $allRecommendations = array_merge(
            $collaborativeRecs,
            $contentBasedRecs,
            $trendingRecs,
            $hybridRecs
        );

        // Remove duplicates and rank by score
        $uniqueRecommendations = $this->deduplicateAndRank($allRecommendations);

        // Take top recommendations
        $topRecommendations = array_slice($uniqueRecommendations, 0, $limit);

        // Save recommendations to database
        foreach ($topRecommendations as $rec) {
            $this->saveRecommendation($user->id, $rec);
        }

        return $topRecommendations;
    }

    /**
     * Collaborative filtering recommendations
     */
    private function getCollaborativeRecommendations(User $user, string $type, int $limit): array
    {
        $recommendations = [];

        // Find users with similar preferences
        $similarUsers = $this->findSimilarUsers($user, $type);

        foreach ($similarUsers as $similarUser) {
            // Get items that similar users liked but current user hasn't seen
            $items = $this->getItemsLikedByUser($similarUser['user_id'], $type);
            
            foreach ($items as $item) {
                $score = $similarUser['similarity'] * $item['rating'];
                
                $recommendations[] = [
                    'item_id' => $item['id'],
                    'type' => $type,
                    'score' => $score,
                    'algorithm' => 'collaborative',
                    'factors' => [
                        'similar_users' => $similarUser['similarity'],
                        'rating' => $item['rating']
                    ]
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Content-based filtering recommendations
     */
    private function getContentBasedRecommendations(User $user, string $type, int $limit): array
    {
        $recommendations = [];
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            return $recommendations;
        }

        // Get user's preferred categories
        $preferredCategories = $preferences->preferred_categories ?? [];
        $preferredLocations = $preferences->preferred_locations ?? [];
        $priceRange = $preferences->price_range ?? [];

        // Query items based on preferences
        $query = $type === 'product' ? Product::query() : Business::query();
        
        if (!empty($preferredCategories)) {
            $query->whereIn('category', $preferredCategories);
        }

        if (!empty($preferredLocations)) {
            $query->whereIn('location', $preferredLocations);
        }

        if (!empty($priceRange)) {
            if ($type === 'product') {
                $query->whereBetween('price', [$priceRange['min'] ?? 0, $priceRange['max'] ?? 999999]);
            } else {
                $query->whereBetween('valuation', [$priceRange['min'] ?? 0, $priceRange['max'] ?? 999999]);
            }
        }

        $items = $query->limit($limit * 2)->get();

        foreach ($items as $item) {
            $score = $this->calculateContentScore($item, $preferences);
            
            $recommendations[] = [
                'item_id' => $item->id,
                'type' => $type,
                'score' => $score,
                'algorithm' => 'content_based',
                'factors' => [
                    'category_match' => in_array($item->category, $preferredCategories ?? []) ? 1 : 0,
                    'location_match' => in_array($item->location ?? '', $preferredLocations ?? []) ? 1 : 0,
                    'price_match' => $this->calculatePriceMatch($item, $priceRange)
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Trending recommendations
     */
    private function getTrendingRecommendations(string $type, int $limit): array
    {
        $recommendations = [];

        // Get trending items based on views, clicks, and recent activity
        $trendingItems = $this->getTrendingItems($type, $limit * 2);

        foreach ($trendingItems as $item) {
            $recommendations[] = [
                'item_id' => $item['id'],
                'type' => $type,
                'score' => $item['trend_score'],
                'algorithm' => 'trending',
                'factors' => [
                    'trending' => $item['trend_score'],
                    'recent_activity' => $item['recent_views']
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Hybrid recommendations combining multiple algorithms
     */
    private function getHybridRecommendations(User $user, string $type, int $limit): array
    {
        $recommendations = [];

        // Get user's recent activity
        $preferences = UserPreference::where('user_id', $user->id)->first();
        $recentViews = $preferences->viewed_items ?? [];

        if (empty($recentViews)) {
            return $recommendations;
        }

        // Find items similar to recently viewed
        foreach (array_slice($recentViews, -5) as $viewedItem) {
            $similarItems = $this->findSimilarItems($viewedItem['item_id'], $type, 5);
            
            foreach ($similarItems as $item) {
                $recommendations[] = [
                    'item_id' => $item['id'],
                    'type' => $type,
                    'score' => $item['similarity'],
                    'algorithm' => 'hybrid',
                    'factors' => [
                        'similar_to_viewed' => $item['similarity'],
                        'recent_interest' => 1
                    ]
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Find users with similar preferences
     */
    private function findSimilarUsers(User $user, string $type): array
    {
        $similarUsers = [];

        // Get current user's preferences
        $userPreferences = UserPreference::where('user_id', $user->id)->first();
        if (!$userPreferences) {
            return $similarUsers;
        }

        // Find other users with similar preferences
        $otherUsers = UserPreference::where('user_id', '!=', $user->id)
            ->whereNotNull('preferred_categories')
            ->get();

        foreach ($otherUsers as $otherUser) {
            $similarity = $this->calculateUserSimilarity($userPreferences, $otherUser);
            
            if ($similarity > 0.3) { // Minimum similarity threshold
                $similarUsers[] = [
                    'user_id' => $otherUser->user_id,
                    'similarity' => $similarity
                ];
            }
        }

        // Sort by similarity (descending)
        usort($similarUsers, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarUsers, 0, 10);
    }

    /**
     * Calculate similarity between two users
     */
    private function calculateUserSimilarity(UserPreference $user1, UserPreference $user2): float
    {
        $similarity = 0;
        $factors = 0;

        // Compare preferred categories
        if (!empty($user1->preferred_categories) && !empty($user2->preferred_categories)) {
            $commonCategories = array_intersect($user1->preferred_categories, $user2->preferred_categories);
            $categorySimilarity = count($commonCategories) / max(count($user1->preferred_categories), count($user2->preferred_categories));
            $similarity += $categorySimilarity;
            $factors++;
        }

        // Compare preferred locations
        if (!empty($user1->preferred_locations) && !empty($user2->preferred_locations)) {
            $commonLocations = array_intersect($user1->preferred_locations, $user2->preferred_locations);
            $locationSimilarity = count($commonLocations) / max(count($user1->preferred_locations), count($user2->preferred_locations));
            $similarity += $locationSimilarity;
            $factors++;
        }

        // Compare price ranges
        if (!empty($user1->price_range) && !empty($user2->price_range)) {
            $priceOverlap = $this->calculateRangeOverlap($user1->price_range, $user2->price_range);
            $similarity += $priceOverlap;
            $factors++;
        }

        return $factors > 0 ? $similarity / $factors : 0;
    }

    /**
     * Get items liked by a specific user
     */
    private function getItemsLikedByUser(int $userId, string $type): array
    {
        $items = [];

        // Get items with high ratings from this user
        $reviews = Review::where('user_id', $userId)
            ->where('reviewable_type', $type === 'product' ? 'App\Models\Product' : 'App\Models\Business')
            ->where('rating', '>=', 4)
            ->with('reviewable')
            ->get();

        foreach ($reviews as $review) {
            $items[] = [
                'id' => $review->reviewable_id,
                'rating' => $review->rating,
                'reviewable' => $review->reviewable
            ];
        }

        return $items;
    }

    /**
     * Calculate content-based score for an item
     */
    private function calculateContentScore($item, UserPreference $preferences): float
    {
        $score = 0;
        $factors = 0;

        // Category match
        if (!empty($preferences->preferred_categories)) {
            if (in_array($item->category, $preferences->preferred_categories)) {
                $score += 0.4;
            }
            $factors++;
        }

        // Location match
        if (!empty($preferences->preferred_locations) && isset($item->location)) {
            if (in_array($item->location, $preferences->preferred_locations)) {
                $score += 0.3;
            }
            $factors++;
        }

        // Price match
        if (!empty($preferences->price_range)) {
            $priceMatch = $this->calculatePriceMatch($item, $preferences->price_range);
            $score += $priceMatch * 0.3;
            $factors++;
        }

        return $factors > 0 ? $score / $factors : 0;
    }

    /**
     * Calculate price match score
     */
    private function calculatePriceMatch($item, array $priceRange): float
    {
        $price = $item->price ?? $item->valuation ?? 0;
        $min = $priceRange['min'] ?? 0;
        $max = $priceRange['max'] ?? 999999;

        if ($price >= $min && $price <= $max) {
            return 1.0;
        } elseif ($price < $min) {
            return max(0, 1 - (($min - $price) / $min));
        } else {
            return max(0, 1 - (($price - $max) / $max));
        }
    }

    /**
     * Get trending items
     */
    private function getTrendingItems(string $type, int $limit): array
    {
        $items = [];

        if ($type === 'product') {
            $trendingProducts = Product::select('*')
                ->selectRaw('(views_count * 0.5 + (CASE WHEN views_count > 0 THEN views_count * 0.3 ELSE 0.1 END)) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingProducts as $product) {
                $items[] = [
                    'id' => $product->id,
                    'trend_score' => $product->trend_score,
                    'recent_views' => $product->views_count ?? 0
                ];
            }
        } else {
            $trendingBusinesses = Business::select('*')
                ->selectRaw('(views_count * 0.5 + (CASE WHEN views_count > 0 THEN views_count * 0.3 ELSE 0.1 END)) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingBusinesses as $business) {
                $items[] = [
                    'id' => $business->id,
                    'trend_score' => $business->trend_score,
                    'recent_views' => $business->views_count ?? 0
                ];
            }
        }

        return $items;
    }

    /**
     * Find items similar to a given item
     */
    private function findSimilarItems(int $itemId, string $type, int $limit): array
    {
        $similarItems = [];

        if ($type === 'product') {
            $item = Product::find($itemId);
            if (!$item) return $similarItems;

            $similar = Product::where('category', $item->category)
                ->where('id', '!=', $itemId)
                ->whereBetween('price', [$item->price * 0.7, $item->price * 1.3])
                ->limit($limit)
                ->get();

            foreach ($similar as $similarItem) {
                $similarity = $this->calculateItemSimilarity($item, $similarItem);
                $similarItems[] = [
                    'id' => $similarItem->id,
                    'similarity' => $similarity
                ];
            }
        } else {
            $item = Business::find($itemId);
            if (!$item) return $similarItems;

            $similar = Business::where('industry', $item->industry)
                ->where('id', '!=', $itemId)
                ->whereBetween('valuation', [$item->valuation * 0.7, $item->valuation * 1.3])
                ->limit($limit)
                ->get();

            foreach ($similar as $similarItem) {
                $similarity = $this->calculateItemSimilarity($item, $similarItem);
                $similarItems[] = [
                    'id' => $similarItem->id,
                    'similarity' => $similarity
                ];
            }
        }

        return $similarItems;
    }

    /**
     * Calculate similarity between two items
     */
    private function calculateItemSimilarity($item1, $item2): float
    {
        $similarity = 0;
        $factors = 0;

        // Category/Industry similarity
        if ($item1->category === $item2->category || $item1->industry === $item2->industry) {
            $similarity += 0.4;
        }
        $factors++;

        // Price/Valuation similarity
        $price1 = $item1->price ?? $item1->valuation ?? 0;
        $price2 = $item2->price ?? $item2->valuation ?? 0;
        
        if ($price1 > 0 && $price2 > 0) {
            $priceDiff = abs($price1 - $price2) / max($price1, $price2);
            $similarity += (1 - $priceDiff) * 0.3;
        }
        $factors++;

        // Rating similarity
        $rating1 = $item1->rating ?? 0;
        $rating2 = $item2->rating ?? 0;
        
        if ($rating1 > 0 && $rating2 > 0) {
            $ratingDiff = abs($rating1 - $rating2) / 5;
            $similarity += (1 - $ratingDiff) * 0.3;
        }
        $factors++;

        return $factors > 0 ? $similarity / $factors : 0;
    }

    /**
     * Calculate overlap between two ranges
     */
    private function calculateRangeOverlap(array $range1, array $range2): float
    {
        $min1 = $range1['min'] ?? 0;
        $max1 = $range1['max'] ?? 999999;
        $min2 = $range2['min'] ?? 0;
        $max2 = $range2['max'] ?? 999999;

        $overlapMin = max($min1, $min2);
        $overlapMax = min($max1, $max2);

        if ($overlapMin > $overlapMax) {
            return 0;
        }

        $overlap = $overlapMax - $overlapMin;
        $totalRange = max($max1, $max2) - min($min1, $min2);

        return $totalRange > 0 ? $overlap / $totalRange : 0;
    }

    /**
     * Remove duplicates and rank recommendations
     */
    private function deduplicateAndRank(array $recommendations): array
    {
        $uniqueRecommendations = [];
        $seen = [];

        foreach ($recommendations as $rec) {
            $key = $rec['type'] . '_' . $rec['item_id'];
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueRecommendations[] = $rec;
            } else {
                // If duplicate found, combine scores
                foreach ($uniqueRecommendations as &$existing) {
                    if ($existing['type'] === $rec['type'] && $existing['item_id'] === $rec['item_id']) {
                        $existing['score'] = ($existing['score'] + $rec['score']) / 2;
                        $existing['factors'] = array_merge($existing['factors'], $rec['factors']);
                        break;
                    }
                }
            }
        }

        // Sort by score (descending)
        usort($uniqueRecommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $uniqueRecommendations;
    }

    /**
     * Save a recommendation to the database
     */
    private function saveRecommendation(int $userId, array $recommendation): void
    {
        try {
            // Normalize score to be between 0 and 1
            $normalizedScore = $this->normalizeScore($recommendation['score'] ?? 0);
            
            Recommendation::create([
                'user_id' => $userId,
                'recommendable_type' => $recommendation['type'] === 'product' ? 'App\Models\Product' : 'App\Models\Business',
                'recommendable_id' => $recommendation['item_id'],
                'score' => $normalizedScore,
                'algorithm_type' => $recommendation['algorithm'] ?? 'unknown',
                'factors' => $recommendation['factors'] ?? [],
                'is_active' => true
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the entire recommendation process
            Log::error('Failed to save recommendation: ' . $e->getMessage());
        }
    }

    /**
     * Normalize score to be between 0 and 1
     */
    private function normalizeScore($score): float
    {
        // If score is already between 0-1, return as is
        if ($score >= 0 && $score <= 1) {
            return (float) $score;
        }
        
        // If score is larger than 1, normalize it
        if ($score > 1) {
            // Use logarithmic scaling for large scores
            return min(0.9999, log($score + 1) / log(101)); // Cap at 0.9999
        }
        
        // If score is negative, return 0
        return 0.0;
    }

    /**
     * Get user preferences
     */
    public function getUserPreferences(User $user): ?UserPreference
    {
        $preferences = UserPreference::where('user_id', $user->id)->first();
        
        if ($preferences) {
            // Add calculated fields
            $preferences->activity_level = $preferences->getActivityLevel();
            $preferences->average_rating = $preferences->getAverageRating();
            $preferences->total_spending = $preferences->getTotalSpending();
            $preferences->most_viewed_categories = $preferences->getMostViewedCategories();
        }
        
        return $preferences;
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences(User $user, array $preferences): UserPreference
    {
        $userPreference = UserPreference::firstOrCreate(['user_id' => $user->id]);
        
        // Update preferences
        if (isset($preferences['categories'])) {
            $userPreference->preferred_categories = $preferences['categories'];
        }
        
        if (isset($preferences['price_range'])) {
            $userPreference->price_range = $preferences['price_range'];
        }
        
        if (isset($preferences['investment_focus'])) {
            $currentInterests = $userPreference->interests ?? [];
            $userPreference->interests = array_merge($currentInterests, $preferences['investment_focus']);
        }
        
        if (isset($preferences['location_preference'])) {
            $userPreference->preferred_locations = [$preferences['location_preference']];
        }
        
        $userPreference->last_activity = now();
        $userPreference->save();
        
        return $userPreference;
    }

    /**
     * Get user recommendations from database
     */
    public function getUserRecommendations(User $user, string $type = 'product', int $limit = 10): array
    {
        // Get existing recommendations
        $recommendableType = $type === 'product' ? 'App\Models\Product' : 'App\Models\Business';
        
        $recommendations = Recommendation::where('user_id', $user->id)
            ->where('recommendable_type', $recommendableType)
            ->where('is_active', true)
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->with(['recommendable'])
            ->get();

        // If we don't have enough recommendations, generate new ones
        if ($recommendations->count() < $limit) {
            $this->generateRecommendations($user, $type, $limit);
            
            // Fetch again after generation
            $recommendations = Recommendation::where('user_id', $user->id)
                ->where('recommendable_type', $recommendableType)
                ->where('is_active', true)
                ->orderBy('score', 'desc')
                ->limit($limit)
                ->with(['recommendable'])
                ->get();
        }

        // Format for API response
        return $recommendations->map(function ($rec) {
            return [
                'id' => $rec->id,
                'item_id' => $rec->recommendable_id,
                'type' => $rec->recommendable_type === 'App\Models\Product' ? 'product' : 'business',
                'score' => $rec->score,
                'algorithm_type' => $rec->algorithm_type,
                'factors' => $rec->factors,
                'recommendable' => $rec->recommendable ? [
                    'id' => $rec->recommendable->id,
                    'name' => $rec->recommendable->title ?? $rec->recommendable->name ?? 'Unknown',
                    'description' => $rec->recommendable->description ?? '',
                    'price' => $rec->recommendable->price ?? $rec->recommendable->valuation ?? 0,
                    'category' => $rec->recommendable->category ?? $rec->recommendable->industry ?? 'Other',
                    'image' => $rec->recommendable->images[0] ?? null
                ] : null
            ];
        })->toArray();
    }

    /**
     * Record user interaction with recommendation
     */
    public function recordInteraction(User $user, int $recommendationId, string $action): bool
    {
        try {
            $recommendation = Recommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$recommendation) {
                return false;
            }

            // Update interaction flags
            switch ($action) {
                case 'view':
                    $recommendation->is_viewed = true;
                    $recommendation->viewed_at = now();
                    break;
                case 'click':
                    $recommendation->is_clicked = true;
                    $recommendation->clicked_at = now();
                    break;
                case 'purchase':
                    $recommendation->is_purchased = true;
                    $recommendation->purchased_at = now();
                    break;
            }

            $recommendation->save();

            // Update user preferences based on interaction
            $this->updatePreferencesFromInteraction($user, $recommendation, $action);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to record recommendation interaction: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user preferences based on interaction
     */
    private function updatePreferencesFromInteraction(User $user, Recommendation $recommendation, string $action): void
    {
        try {
            $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);
            
            // Get the recommended item
            $item = $recommendation->recommendable;
            if (!$item) return;

            // Update click behavior
            $clickBehavior = $preferences->click_behavior ?? [];
            if (!isset($clickBehavior[$action])) {
                $clickBehavior[$action] = [];
            }
            
            $category = $item->category ?? $item->industry ?? 'other';
            $clickBehavior[$action][$category] = ($clickBehavior[$action][$category] ?? 0) + 1;
            $preferences->click_behavior = $clickBehavior;

            // Update viewed items for better recommendations
            $viewedItems = $preferences->viewed_items ?? [];
            $viewedItems[] = [
                'item_id' => $item->id,
                'type' => $recommendation->recommendable_type,
                'category' => $category,
                'timestamp' => now()->toISOString()
            ];
            
            // Keep only last 100 viewed items
            $preferences->viewed_items = array_slice($viewedItems, -100);
            $preferences->last_activity = now();
            $preferences->save();
        } catch (\Exception $e) {
            Log::error('Failed to update preferences from interaction: ' . $e->getMessage());
        }
    }
}
