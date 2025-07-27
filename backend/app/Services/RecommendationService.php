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
                ->selectRaw('(views * 0.3 + clicks * 0.5 + (rating * review_count) * 0.2) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingProducts as $product) {
                $items[] = [
                    'id' => $product->id,
                    'trend_score' => $product->trend_score,
                    'recent_views' => $product->views ?? 0
                ];
            }
        } else {
            $trendingBusinesses = Business::select('*')
                ->selectRaw('(views * 0.3 + clicks * 0.5 + (rating * review_count) * 0.2) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingBusinesses as $business) {
                $items[] = [
                    'id' => $business->id,
                    'trend_score' => $business->trend_score,
                    'recent_views' => $business->views ?? 0
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
     * Save recommendation to database
     */
    private function saveRecommendation(int $userId, array $recommendation): void
    {
        // Check if recommendation already exists
        $existing = Recommendation::where('user_id', $userId)
            ->where('recommendable_type', $recommendation['type'] === 'product' ? 'App\Models\Product' : 'App\Models\Business')
            ->where('recommendable_id', $recommendation['item_id'])
            ->first();

        if ($existing) {
            // Update existing recommendation
            $existing->update([
                'score' => $recommendation['score'],
                'algorithm_type' => $recommendation['algorithm'],
                'factors' => $recommendation['factors'],
                'is_active' => true
            ]);
        } else {
            // Create new recommendation
            Recommendation::create([
                'user_id' => $userId,
                'recommendable_type' => $recommendation['type'] === 'product' ? 'App\Models\Product' : 'App\Models\Business',
                'recommendable_id' => $recommendation['item_id'],
                'score' => $recommendation['score'],
                'algorithm_type' => $recommendation['algorithm'],
                'factors' => $recommendation['factors'],
                'is_active' => true
            ]);
        }
    }

    /**
     * Get recommendations for a user
     */
    public function getUserRecommendations(User $user, string $type = 'product', int $limit = 10): array
    {
        $recommendations = Recommendation::where('user_id', $user->id)
            ->where('recommendable_type', $type === 'product' ? 'App\Models\Product' : 'App\Models\Business')
            ->where('is_active', true)
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->with('recommendable')
            ->get();

        return $recommendations->toArray();
    }

    /**
     * Update user preferences based on activity
     */
    public function updateUserPreferences(User $user, string $action, array $data): void
    {
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);

        switch ($action) {
            case 'view':
                $preferences->addViewedItem($data['type'], $data['item_id'], $data['metadata'] ?? []);
                break;
            case 'search':
                $preferences->addSearchTerm($data['term'], $data['metadata'] ?? []);
                break;
            case 'purchase':
                $preferences->addPurchase($data['type'], $data['item_id'], $data['amount'], $data['metadata'] ?? []);
                break;
            case 'rate':
                $preferences->addRating($data['type'], $data['item_id'], $data['rating'], $data['metadata'] ?? []);
                break;
        }
    }
} 

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Business;
use App\Models\Recommendation;
use App\Models\UserPreference;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

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
                ->selectRaw('(views * 0.3 + clicks * 0.5 + (rating * review_count) * 0.2) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingProducts as $product) {
                $items[] = [
                    'id' => $product->id,
                    'trend_score' => $product->trend_score,
                    'recent_views' => $product->views ?? 0
                ];
            }
        } else {
            $trendingBusinesses = Business::select('*')
                ->selectRaw('(views * 0.3 + clicks * 0.5 + (rating * review_count) * 0.2) as trend_score')
                ->orderBy('trend_score', 'desc')
                ->limit($limit)
                ->get();

            foreach ($trendingBusinesses as $business) {
                $items[] = [
                    'id' => $business->id,
                    'trend_score' => $business->trend_score,
                    'recent_views' => $business->views ?? 0
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
     * Save recommendation to database
     */
    private function saveRecommendation(int $userId, array $recommendation): void
    {
        // Check if recommendation already exists
        $existing = Recommendation::where('user_id', $userId)
            ->where('recommendable_type', $recommendation['type'] === 'product' ? 'App\Models\Product' : 'App\Models\Business')
            ->where('recommendable_id', $recommendation['item_id'])
            ->first();

        if ($existing) {
            // Update existing recommendation
            $existing->update([
                'score' => $recommendation['score'],
                'algorithm_type' => $recommendation['algorithm'],
                'factors' => $recommendation['factors'],
                'is_active' => true
            ]);
        } else {
            // Create new recommendation
            Recommendation::create([
                'user_id' => $userId,
                'recommendable_type' => $recommendation['type'] === 'product' ? 'App\Models\Product' : 'App\Models\Business',
                'recommendable_id' => $recommendation['item_id'],
                'score' => $recommendation['score'],
                'algorithm_type' => $recommendation['algorithm'],
                'factors' => $recommendation['factors'],
                'is_active' => true
            ]);
        }
    }

    /**
     * Get recommendations for a user
     */
    public function getUserRecommendations(User $user, string $type = 'product', int $limit = 10): array
    {
        $recommendations = Recommendation::where('user_id', $user->id)
            ->where('recommendable_type', $type === 'product' ? 'App\Models\Product' : 'App\Models\Business')
            ->where('is_active', true)
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->with('recommendable')
            ->get();

        return $recommendations->toArray();
    }

    /**
     * Update user preferences based on activity
     */
    public function updateUserPreferences(User $user, string $action, array $data): void
    {
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);

        switch ($action) {
            case 'view':
                $preferences->addViewedItem($data['type'], $data['item_id'], $data['metadata'] ?? []);
                break;
            case 'search':
                $preferences->addSearchTerm($data['term'], $data['metadata'] ?? []);
                break;
            case 'purchase':
                $preferences->addPurchase($data['type'], $data['item_id'], $data['amount'], $data['metadata'] ?? []);
                break;
            case 'rate':
                $preferences->addRating($data['type'], $data['item_id'], $data['rating'], $data['metadata'] ?? []);
                break;
        }
    }
} 