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

            // Validate action
            $validActions = ['view', 'click', 'purchase', 'dismiss'];
            if (!in_array($action, $validActions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action. Must be one of: ' . implode(', ', $validActions)
                ], 400);
            }

            // Record the interaction
            $this->recommendationService->recordInteraction($user, $recommendationId, $action);

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
     * Get user preferences for recommendations
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = $this->recommendationService->getUserPreferences($user);

            return response()->json([
                'success' => true,
                'data' => $preferences
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
     * Update user preferences for recommendations
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = $request->validate([
                'categories' => 'sometimes|array',
                'price_range' => 'sometimes|array',
                'location_preference' => 'sometimes|string',
                'investment_focus' => 'sometimes|array',
                'exclude_sold' => 'sometimes|boolean',
                'notification_frequency' => 'sometimes|string|in:daily,weekly,monthly'
            ]);

            $updatedPreferences = $this->recommendationService->updateUserPreferences($user, $preferences);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'data' => $updatedPreferences
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
     * Record user activity for recommendation learning
     */
    public function recordActivity(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $activity = $request->validate([
                'type' => 'required|string|in:view,search,filter,sort,click,purchase',
                'item_id' => 'required|string',
                'item_type' => 'required|string|in:product,business',
                'metadata' => 'sometimes|array'
            ]);

            $this->recommendationService->recordActivity($user, $activity);

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
     * Get recommendation analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $period = $request->get('period', '30d'); // 7d, 30d, 90d
            $type = $request->get('type', 'product');

            $analytics = $this->recommendationService->getAnalytics($user, $period, $type);

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