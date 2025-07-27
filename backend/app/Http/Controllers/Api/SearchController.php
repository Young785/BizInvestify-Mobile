<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search products with advanced filtering
     */
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $result = $this->searchService->searchProducts($request);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'stats' => $result['stats'],
                'filters' => $result['filters']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search businesses with advanced filtering
     */
    public function searchBusinesses(Request $request): JsonResponse
    {
        try {
            $result = $this->searchService->searchBusinesses($request);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'stats' => $result['stats'],
                'filters' => $result['filters']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $result = $this->searchService->getTrendingProducts($limit);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'period' => $result['period']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trending products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending businesses
     */
    public function getTrendingBusinesses(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $result = $this->searchService->getTrendingBusinesses($limit);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'period' => $result['period']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trending businesses',
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
            $user = $request->user();
            $type = $request->get('type', 'product');
            $limit = $request->get('limit', 10);

            $result = $this->searchService->getRecommendations($user->id, $type, $limit);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'reason' => $result['reason']
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
     * Get search suggestions
     */
    public function getSearchSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $type = $request->get('type', 'product');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $suggestions = $this->getSuggestions($query, $type);

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions based on query
     */
    private function getSuggestions(string $query, string $type): array
    {
        $suggestions = [];

        if ($type === 'product') {
            // Product name suggestions
            $productNames = \App\Models\Product::where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->select('name')
                ->distinct()
                ->limit(5)
                ->pluck('name');

            // Category suggestions
            $categories = \App\Models\Product::where('status', 'active')
                ->where('category', 'LIKE', "%{$query}%")
                ->select('category')
                ->distinct()
                ->limit(5)
                ->pluck('category');

            // Tag suggestions
            $tags = \App\Models\Product::where('status', 'active')
                ->where('tags', 'LIKE', "%{$query}%")
                ->select('tags')
                ->distinct()
                ->limit(5)
                ->pluck('tags')
                ->flatten()
                ->filter()
                ->unique();

            $suggestions = [
                'names' => $productNames,
                'categories' => $categories,
                'tags' => $tags->take(5)->values()
            ];

        } else {
            // Business name suggestions
            $businessNames = \App\Models\Business::where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->select('name')
                ->distinct()
                ->limit(5)
                ->pluck('name');

            // Industry suggestions
            $industries = \App\Models\Business::where('status', 'active')
                ->where('industry', 'LIKE', "%{$query}%")
                ->select('industry')
                ->distinct()
                ->limit(5)
                ->pluck('industry');

            $suggestions = [
                'names' => $businessNames,
                'industries' => $industries
            ];
        }

        return $suggestions;
    }

    /**
     * Get search filters
     */
    public function getSearchFilters(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'product');

            if ($type === 'product') {
                $filters = $this->searchService->getAvailableFilters($request);
            } else {
                $filters = $this->searchService->getAvailableBusinessFilters($request);
            }

            return response()->json([
                'success' => true,
                'data' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get filters',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 