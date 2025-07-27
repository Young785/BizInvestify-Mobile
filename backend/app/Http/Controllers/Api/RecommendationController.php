<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\UserPreference;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized recommendations for the user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->get('type', 'product');
            $limit = min($request->get('limit', 10), 50); // Max 50 recommendations
            $algorithm = $request->get('algorithm'); // Optional algorithm filter

            // Get existing recommendations or generate new ones
            $recommendations = $this->recommendationService->getUserRecommendations($user, $type, $limit);

            // Filter by algorithm if specified
            if ($algorithm) {
                $recommendations = array_filter($recommendations, function ($rec) use ($algorithm) {
                    return $rec['algorithm_type'] === $algorithm;
                });
            }

            // Mark recommendations as viewed
            foreach ($recommendations as $recommendation) {
                if (isset($recommendation['id'])) {
                    $rec = Recommendation::find($recommendation['id']);
                    if ($rec) {
                        $rec->markAsViewed();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate new recommendations for the user
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->get('type', 'product');
            $limit = min($request->get('limit', 10), 50);

            // Generate new recommendations
            $recommendations = $this->recommendationService->generateRecommendations($user, $type, $limit);

            return response()->json([
                'success' => true,
                'message' => 'Recommendations generated successfully',
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record user interaction with a recommendation
     */
    public function recordInteraction(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $recommendationId = $request->get('recommendation_id');
            $action = $request->get('action'); // 'view', 'click', 'purchase'

            if (!$recommendationId || !$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation ID and action are required'
                ], 400);
            }

            $recommendation = Recommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$recommendation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation not found'
                ], 404);
            }

            // Record the interaction
            switch ($action) {
                case 'view':
                    $recommendation->markAsViewed();
                    break;
                case 'click':
                    $recommendation->markAsClicked();
                    break;
                case 'purchase':
                    $recommendation->markAsPurchased();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }

            // Update user preferences based on the interaction
            $this->recommendationService->updateUserPreferences($user, 'view', [
                'type' => $recommendation->recommendable_type === 'App\Models\Product' ? 'product' : 'business',
                'item_id' => $recommendation->recommendable_id,
                'metadata' => [
                    'recommendation_id' => $recommendation->id,
                    'algorithm_type' => $recommendation->algorithm_type,
                    'score' => $recommendation->score
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interaction recorded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record interaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = UserPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'interests' => [],
                        'preferred_categories' => [],
                        'preferred_locations' => [],
                        'price_range' => null,
                        'investment_range' => null,
                        'most_viewed_categories' => [],
                        'most_searched_terms' => [],
                        'average_rating' => 0,
                        'total_spending' => 0,
                        'activity_level' => 'inactive'
                    ]
                ]);
            }

            $data = [
                'interests' => $preferences->interests ?? [],
                'preferred_categories' => $preferences->preferred_categories ?? [],
                'preferred_locations' => $preferences->preferred_locations ?? [],
                'price_range' => $preferences->price_range,
                'investment_range' => $preferences->investment_range,
                'most_viewed_categories' => $preferences->getMostViewedCategories(),
                'most_searched_terms' => $preferences->getMostSearchedTerms(),
                'average_rating' => $preferences->getAverageRating(),
                'total_spending' => $preferences->getTotalSpending(),
                'activity_level' => $preferences->getActivityLevel()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);

            $data = $request->only([
                'interests',
                'preferred_categories',
                'preferred_locations',
                'price_range',
                'investment_range'
            ]);

            // Validate price range
            if (isset($data['price_range'])) {
                if (!isset($data['price_range']['min']) || !isset($data['price_range']['max'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Price range must include min and max values'
                    ], 400);
                }
            }

            // Validate investment range
            if (isset($data['investment_range'])) {
                if (!isset($data['investment_range']['min']) || !isset($data['investment_range']['max'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Investment range must include min and max values'
                    ], 400);
                }
            }

            $preferences->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'data' => $preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record user activity (view, search, purchase, rating)
     */
    public function recordActivity(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $action = $request->get('action');
            $data = $request->get('data', []);

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action is required'
                ], 400);
            }

            // Validate required data for each action
            switch ($action) {
                case 'view':
                    if (!isset($data['type']) || !isset($data['item_id'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type and item_id are required for view action'
                        ], 400);
                    }
                    break;
                case 'search':
                    if (!isset($data['term'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Search term is required'
                        ], 400);
                    }
                    break;
                case 'purchase':
                    if (!isset($data['type']) || !isset($data['item_id']) || !isset($data['amount'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type, item_id, and amount are required for purchase action'
                        ], 400);
                    }
                    break;
                case 'rate':
                    if (!isset($data['type']) || !isset($data['item_id']) || !isset($data['rating'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type, item_id, and rating are required for rate action'
                        ], 400);
                    }
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }

            // Update user preferences
            $this->recommendationService->updateUserPreferences($user, $action, $data);

            return response()->json([
                'success' => true,
                'message' => 'Activity recorded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommendation performance analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $timeRange = $request->get('time_range', '30d'); // 7d, 30d, 90d

            // Calculate date range
            $endDate = now();
            switch ($timeRange) {
                case '7d':
                    $startDate = now()->subDays(7);
                    break;
                case '30d':
                    $startDate = now()->subDays(30);
                    break;
                case '90d':
                    $startDate = now()->subDays(90);
                    break;
                default:
                    $startDate = now()->subDays(30);
            }

            // Get recommendations in date range
            $recommendations = Recommendation::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Calculate metrics
            $totalRecommendations = $recommendations->count();
            $viewedRecommendations = $recommendations->where('is_viewed', true)->count();
            $clickedRecommendations = $recommendations->where('is_clicked', true)->count();
            $purchasedRecommendations = $recommendations->where('is_purchased', true)->count();

            $viewRate = $totalRecommendations > 0 ? ($viewedRecommendations / $totalRecommendations) * 100 : 0;
            $clickRate = $viewedRecommendations > 0 ? ($clickedRecommendations / $viewedRecommendations) * 100 : 0;
            $conversionRate = $clickedRecommendations > 0 ? ($purchasedRecommendations / $clickedRecommendations) * 100 : 0;

            // Algorithm performance
            $algorithmPerformance = [];
            $algorithms = $recommendations->pluck('algorithm_type')->unique();
            
            foreach ($algorithms as $algorithm) {
                $algoRecs = $recommendations->where('algorithm_type', $algorithm);
                $algoTotal = $algoRecs->count();
                $algoViewed = $algoRecs->where('is_viewed', true)->count();
                $algoClicked = $algoRecs->where('is_clicked', true)->count();
                $algoPurchased = $algoRecs->where('is_purchased', true)->count();

                $algorithmPerformance[$algorithm] = [
                    'total' => $algoTotal,
                    'viewed' => $algoViewed,
                    'clicked' => $algoClicked,
                    'purchased' => $algoPurchased,
                    'view_rate' => $algoTotal > 0 ? ($algoViewed / $algoTotal) * 100 : 0,
                    'click_rate' => $algoViewed > 0 ? ($algoClicked / $algoViewed) * 100 : 0,
                    'conversion_rate' => $algoClicked > 0 ? ($algoPurchased / $algoClicked) * 100 : 0
                ];
            }

            $analytics = [
                'time_range' => $timeRange,
                'total_recommendations' => $totalRecommendations,
                'viewed_recommendations' => $viewedRecommendations,
                'clicked_recommendations' => $clickedRecommendations,
                'purchased_recommendations' => $purchasedRecommendations,
                'view_rate' => round($viewRate, 2),
                'click_rate' => round($clickRate, 2),
                'conversion_rate' => round($conversionRate, 2),
                'algorithm_performance' => $algorithmPerformance
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\UserPreference;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized recommendations for the user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->get('type', 'product');
            $limit = min($request->get('limit', 10), 50); // Max 50 recommendations
            $algorithm = $request->get('algorithm'); // Optional algorithm filter

            // Get existing recommendations or generate new ones
            $recommendations = $this->recommendationService->getUserRecommendations($user, $type, $limit);

            // Filter by algorithm if specified
            if ($algorithm) {
                $recommendations = array_filter($recommendations, function ($rec) use ($algorithm) {
                    return $rec['algorithm_type'] === $algorithm;
                });
            }

            // Mark recommendations as viewed
            foreach ($recommendations as $recommendation) {
                if (isset($recommendation['id'])) {
                    $rec = Recommendation::find($recommendation['id']);
                    if ($rec) {
                        $rec->markAsViewed();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate new recommendations for the user
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->get('type', 'product');
            $limit = min($request->get('limit', 10), 50);

            // Generate new recommendations
            $recommendations = $this->recommendationService->generateRecommendations($user, $type, $limit);

            return response()->json([
                'success' => true,
                'message' => 'Recommendations generated successfully',
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record user interaction with a recommendation
     */
    public function recordInteraction(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $recommendationId = $request->get('recommendation_id');
            $action = $request->get('action'); // 'view', 'click', 'purchase'

            if (!$recommendationId || !$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation ID and action are required'
                ], 400);
            }

            $recommendation = Recommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$recommendation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation not found'
                ], 404);
            }

            // Record the interaction
            switch ($action) {
                case 'view':
                    $recommendation->markAsViewed();
                    break;
                case 'click':
                    $recommendation->markAsClicked();
                    break;
                case 'purchase':
                    $recommendation->markAsPurchased();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }

            // Update user preferences based on the interaction
            $this->recommendationService->updateUserPreferences($user, 'view', [
                'type' => $recommendation->recommendable_type === 'App\Models\Product' ? 'product' : 'business',
                'item_id' => $recommendation->recommendable_id,
                'metadata' => [
                    'recommendation_id' => $recommendation->id,
                    'algorithm_type' => $recommendation->algorithm_type,
                    'score' => $recommendation->score
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interaction recorded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record interaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = UserPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'interests' => [],
                        'preferred_categories' => [],
                        'preferred_locations' => [],
                        'price_range' => null,
                        'investment_range' => null,
                        'most_viewed_categories' => [],
                        'most_searched_terms' => [],
                        'average_rating' => 0,
                        'total_spending' => 0,
                        'activity_level' => 'inactive'
                    ]
                ]);
            }

            $data = [
                'interests' => $preferences->interests ?? [],
                'preferred_categories' => $preferences->preferred_categories ?? [],
                'preferred_locations' => $preferences->preferred_locations ?? [],
                'price_range' => $preferences->price_range,
                'investment_range' => $preferences->investment_range,
                'most_viewed_categories' => $preferences->getMostViewedCategories(),
                'most_searched_terms' => $preferences->getMostSearchedTerms(),
                'average_rating' => $preferences->getAverageRating(),
                'total_spending' => $preferences->getTotalSpending(),
                'activity_level' => $preferences->getActivityLevel()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);

            $data = $request->only([
                'interests',
                'preferred_categories',
                'preferred_locations',
                'price_range',
                'investment_range'
            ]);

            // Validate price range
            if (isset($data['price_range'])) {
                if (!isset($data['price_range']['min']) || !isset($data['price_range']['max'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Price range must include min and max values'
                    ], 400);
                }
            }

            // Validate investment range
            if (isset($data['investment_range'])) {
                if (!isset($data['investment_range']['min']) || !isset($data['investment_range']['max'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Investment range must include min and max values'
                    ], 400);
                }
            }

            $preferences->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'data' => $preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record user activity (view, search, purchase, rating)
     */
    public function recordActivity(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $action = $request->get('action');
            $data = $request->get('data', []);

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action is required'
                ], 400);
            }

            // Validate required data for each action
            switch ($action) {
                case 'view':
                    if (!isset($data['type']) || !isset($data['item_id'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type and item_id are required for view action'
                        ], 400);
                    }
                    break;
                case 'search':
                    if (!isset($data['term'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Search term is required'
                        ], 400);
                    }
                    break;
                case 'purchase':
                    if (!isset($data['type']) || !isset($data['item_id']) || !isset($data['amount'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type, item_id, and amount are required for purchase action'
                        ], 400);
                    }
                    break;
                case 'rate':
                    if (!isset($data['type']) || !isset($data['item_id']) || !isset($data['rating'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Type, item_id, and rating are required for rate action'
                        ], 400);
                    }
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }

            // Update user preferences
            $this->recommendationService->updateUserPreferences($user, $action, $data);

            return response()->json([
                'success' => true,
                'message' => 'Activity recorded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommendation performance analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $timeRange = $request->get('time_range', '30d'); // 7d, 30d, 90d

            // Calculate date range
            $endDate = now();
            switch ($timeRange) {
                case '7d':
                    $startDate = now()->subDays(7);
                    break;
                case '30d':
                    $startDate = now()->subDays(30);
                    break;
                case '90d':
                    $startDate = now()->subDays(90);
                    break;
                default:
                    $startDate = now()->subDays(30);
            }

            // Get recommendations in date range
            $recommendations = Recommendation::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Calculate metrics
            $totalRecommendations = $recommendations->count();
            $viewedRecommendations = $recommendations->where('is_viewed', true)->count();
            $clickedRecommendations = $recommendations->where('is_clicked', true)->count();
            $purchasedRecommendations = $recommendations->where('is_purchased', true)->count();

            $viewRate = $totalRecommendations > 0 ? ($viewedRecommendations / $totalRecommendations) * 100 : 0;
            $clickRate = $viewedRecommendations > 0 ? ($clickedRecommendations / $viewedRecommendations) * 100 : 0;
            $conversionRate = $clickedRecommendations > 0 ? ($purchasedRecommendations / $clickedRecommendations) * 100 : 0;

            // Algorithm performance
            $algorithmPerformance = [];
            $algorithms = $recommendations->pluck('algorithm_type')->unique();
            
            foreach ($algorithms as $algorithm) {
                $algoRecs = $recommendations->where('algorithm_type', $algorithm);
                $algoTotal = $algoRecs->count();
                $algoViewed = $algoRecs->where('is_viewed', true)->count();
                $algoClicked = $algoRecs->where('is_clicked', true)->count();
                $algoPurchased = $algoRecs->where('is_purchased', true)->count();

                $algorithmPerformance[$algorithm] = [
                    'total' => $algoTotal,
                    'viewed' => $algoViewed,
                    'clicked' => $algoClicked,
                    'purchased' => $algoPurchased,
                    'view_rate' => $algoTotal > 0 ? ($algoViewed / $algoTotal) * 100 : 0,
                    'click_rate' => $algoViewed > 0 ? ($algoClicked / $algoViewed) * 100 : 0,
                    'conversion_rate' => $algoClicked > 0 ? ($algoPurchased / $algoClicked) * 100 : 0
                ];
            }

            $analytics = [
                'time_range' => $timeRange,
                'total_recommendations' => $totalRecommendations,
                'viewed_recommendations' => $viewedRecommendations,
                'clicked_recommendations' => $clickedRecommendations,
                'purchased_recommendations' => $purchasedRecommendations,
                'view_rate' => round($viewRate, 2),
                'click_rate' => round($clickRate, 2),
                'conversion_rate' => round($conversionRate, 2),
                'algorithm_performance' => $algorithmPerformance
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 