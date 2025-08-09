<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MarketplaceController extends Controller
{
    protected $marketplaceService;

    public function __construct(MarketplaceService $marketplaceService)
    {
        $this->marketplaceService = $marketplaceService;
    }

    /**
     * Get marketplace overview statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->marketplaceService->getMarketplaceStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve marketplace stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get personalized recommendations
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $type = $request->get('type');
            $limit = $request->get('limit', 10);

            $recommendations = $this->marketplaceService->getPersonalizedRecommendations(
                $user->id,
                $type,
                $limit
            );

            return response()->json([
                'success' => true,
                'data' => $recommendations['data'],
                'reason' => $recommendations['reason']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured listings
     */
    public function getFeaturedListings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type');
            $limit = $request->get('limit', 10);

            $featuredListings = $this->marketplaceService->getFeaturedListings($type, $limit);

            return response()->json([
                'success' => true,
                'data' => $featuredListings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get featured listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketplace analytics (admin only)
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user has admin permissions
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $analytics = $this->marketplaceService->getMarketplaceAnalytics($request);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search insights (admin only)
     */
    public function getSearchInsights(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user has admin permissions
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $insights = $this->marketplaceService->getSearchInsights($request);

            return response()->json([
                'success' => true,
                'data' => $insights
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get search insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketplace health metrics (admin only)
     */
    public function getHealth(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user has admin permissions
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $health = $this->marketplaceService->getMarketplaceHealth();

            return response()->json([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get health metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending items
     */
    public function getTrending(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'period' => 'nullable|in:day,week,month',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type');
            $period = $request->get('period', 'week');
            $limit = $request->get('limit', 10);

            $trending = $this->getTrendingItems($type, $period, $limit);

            return response()->json([
                'success' => true,
                'data' => $trending
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trending items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketplace categories and industries
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'product');

            if ($type === 'product') {
                $categories = \App\Models\Product::where('status', 'active')
                    ->select('category')
                    ->distinct()
                    ->pluck('category')
                    ->filter()
                    ->values();
            } else {
                $categories = \App\Models\Business::where('status', 'active')
                    ->select('industry')
                    ->distinct()
                    ->pluck('industry')
                    ->filter()
                    ->values();
            }

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketplace insights for users
     */
    public function getInsights(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $insights = [
                'user_stats' => $this->getUserStats($user),
                'recent_activity' => $this->getRecentActivity($user),
                'suggestions' => $this->getUserSuggestions($user),
            ];

            return response()->json([
                'success' => true,
                'data' => $insights
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending items based on period
     */
    private function getTrendingItems(string $type, string $period, int $limit): array
    {
        $startDate = now();
        
        switch ($period) {
            case 'day':
                $startDate = now()->subDay();
                break;
            case 'week':
                $startDate = now()->subWeek();
                break;
            case 'month':
                $startDate = now()->subMonth();
                break;
        }

        if ($type === 'product') {
            $trending = \App\Models\Product::where('status', 'active')
                ->where('created_at', '>=', $startDate)
                ->orderBy('views_count', 'desc')
                ->orderBy('average_rating', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();
        } else {
            $trending = \App\Models\Business::where('status', 'active')
                ->where('created_at', '>=', $startDate)
                ->orderBy('views_count', 'desc')
                ->orderBy('valuation', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();
        }

        return $trending->toArray();
    }

    /**
     * Get user statistics
     */
    private function getUserStats($user): array
    {
        $stats = [
            'total_listings' => 0,
            'total_sales' => 0,
            'total_investments' => 0,
            'total_reviews' => 0,
            'wishlist_count' => 0,
        ];

        if ($user->hasRole('seller')) {
            $stats['total_listings'] = \App\Models\Product::where('user_id', $user->id)->count() +
                                     \App\Models\Business::where('user_id', $user->id)->count();
            
            $stats['total_sales'] = \App\Models\Transaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->where('status', 'completed')
                ->count();
        }

        if ($user->hasRole('buyer')) {
            $stats['total_investments'] = \App\Models\Investment::where('investor_id', $user->id)
                ->where('status', 'completed')
                ->count();
        }

        $stats['total_reviews'] = \App\Models\Review::where('user_id', $user->id)->count();
        $stats['wishlist_count'] = \App\Models\Wishlist::where('user_id', $user->id)->count();

        return $stats;
    }

    /**
     * Get recent activity for user
     */
    private function getRecentActivity($user): array
    {
        $activities = [];

        // Recent transactions
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($transactions as $transaction) {
            $activities[] = [
                'type' => 'transaction',
                'title' => ucfirst($transaction->type) . ' completed',
                'description' => '$' . number_format($transaction->amount, 2),
                'date' => $transaction->created_at,
                'icon' => 'credit-card'
            ];
        }

        // Recent reviews
        $reviews = \App\Models\Review::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($reviews as $review) {
            $activities[] = [
                'type' => 'review',
                'title' => 'Review posted',
                'description' => $review->rating . ' stars',
                'date' => $review->created_at,
                'icon' => 'star'
            ];
        }

        // Sort by date and return top 10
        usort($activities, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get personalized suggestions for user
     */
    private function getUserSuggestions($user): array
    {
        $suggestions = [];

        // Complete profile suggestion
        if (!$user->phone_verified_at) {
            $suggestions[] = [
                'type' => 'profile',
                'title' => 'Verify your phone number',
                'description' => 'Complete your profile verification for better trust',
                'action' => 'verify_phone',
                'priority' => 'high'
            ];
        }

        // KYC suggestion
        if ($user->kyc_status !== 'verified') {
            $suggestions[] = [
                'type' => 'kyc',
                'title' => 'Complete KYC verification',
                'description' => 'Get verified to unlock all features',
                'action' => 'complete_kyc',
                'priority' => 'high'
            ];
        }

        // Add listing suggestion for sellers
        if ($user->hasRole('seller')) {
            $productCount = \App\Models\Product::where('user_id', $user->id)->count();
            $businessCount = \App\Models\Business::where('user_id', $user->id)->count();
            
            if ($productCount === 0) {
                $suggestions[] = [
                    'type' => 'listing',
                    'title' => 'List your first product',
                    'description' => 'Start selling by creating your first product listing',
                    'action' => 'add_product',
                    'priority' => 'medium'
                ];
            }
            
            if ($businessCount === 0) {
                $suggestions[] = [
                    'type' => 'listing',
                    'title' => 'List your business',
                    'description' => 'Attract investors by listing your business',
                    'action' => 'add_business',
                    'priority' => 'medium'
                ];
            }
        }

        // Add review suggestion
        $recentPurchases = \App\Models\Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($recentPurchases > 0) {
            $suggestions[] = [
                'type' => 'review',
                'title' => 'Leave a review',
                'description' => 'Share your experience with recent purchases',
                'action' => 'leave_review',
                'priority' => 'low'
            ];
        }

        return $suggestions;
    }

    /**
     * Get public categories (no authentication required).
     */
    public function getPublicCategories(): JsonResponse
    {
        try {
            $categories = [
                'Technology' => ['Software', 'Hardware', 'Mobile Apps', 'Web Services', 'AI/ML'],
                'Healthcare' => ['Medical Devices', 'Pharmaceuticals', 'Telemedicine', 'Wellness'],
                'Food & Beverage' => ['Restaurants', 'Food Delivery', 'Beverages', 'Catering'],
                'Retail' => ['E-commerce', 'Fashion', 'Home & Garden', 'Electronics'],
                'Finance' => ['Fintech', 'Insurance', 'Investment', 'Banking'],
                'Education' => ['EdTech', 'Training', 'Online Learning', 'Tutoring'],
                'Manufacturing' => ['Industrial', 'Automotive', 'Textiles', 'Chemicals'],
                'Real Estate' => ['Residential', 'Commercial', 'Property Management', 'Construction'],
                'Transportation' => ['Logistics', 'Ride-sharing', 'Delivery', 'Freight'],
                'Entertainment' => ['Media', 'Gaming', 'Events', 'Streaming'],
                'Energy' => ['Renewable', 'Oil & Gas', 'Utilities', 'Clean Tech'],
                'Other' => ['Consulting', 'Legal', 'Marketing', 'Non-profit']
            ];

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public featured listings (no authentication required).
     */
    public function getPublicFeatured(): JsonResponse
    {
        try {
            $featuredProducts = \App\Models\Product::with(['seller'])
                ->where('status', 'active')
                ->where('featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            $featuredBusinesses = \App\Models\Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->where('featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            // Calculate funding progress for businesses
            foreach ($featuredBusinesses as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $featuredProducts,
                    'businesses' => $featuredBusinesses
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public trending items (no authentication required).
     */
    public function getPublicTrending(): JsonResponse
    {
        try {
            $trendingProducts = \App\Models\Product::with(['seller'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $trendingBusinesses = \App\Models\Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Calculate funding progress for businesses
            foreach ($trendingBusinesses as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $trendingProducts,
                    'businesses' => $trendingBusinesses
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public marketplace stats (no authentication required).
     */
    public function getPublicStats(): JsonResponse
    {
        try {
            $stats = $this->marketplaceService->getMarketplaceStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve marketplace stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public marketplace recommendations (no authentication required).
     */
    public function getPublicRecommendations(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type');
            $limit = $request->get('limit', 10);

            // For public recommendations, we'll return trending items instead of personalized ones
            if ($type === 'product') {
                $recommendations = \App\Models\Product::with(['seller'])
                    ->where('status', 'active')
                    ->orderBy('views_count', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $recommendations = \App\Models\Business::with(['seller'])
                    ->where('status', 'active')
                    ->orderBy('views_count', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'reason' => 'Based on trending items'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 