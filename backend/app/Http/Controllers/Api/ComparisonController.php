<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ComparisonController extends Controller
{
    /**
     * Get current comparison session
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'session_id' => 'nullable|string'
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
            $sessionId = $request->get('session_id');

            $comparison = Comparison::getOrCreateSession(
                $user ? $user->id : null,
                $sessionId,
                $type
            );

            $comparisonData = $comparison->getComparisonData();

            return response()->json([
                'success' => true,
                'data' => $comparisonData,
                'session_id' => $comparison->session_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to comparison
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'session_id' => 'nullable|string'
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
            $itemId = $request->get('item_id');
            $sessionId = $request->get('session_id');

            // Check if item exists
            if ($type === 'product') {
                $item = Product::find($itemId);
            } else {
                $item = Business::find($itemId);
            }

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $comparison = Comparison::getOrCreateSession($user ? $user->id : null, $sessionId, $type);
            
            if ($comparison->hasItem($itemId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is already in comparison'
                ], 400);
            }

            if (!$comparison->addItem($itemId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comparison is full (maximum 4 items)'
                ], 400);
            }

            $comparisonData = $comparison->getComparisonData();

            return response()->json([
                'success' => true,
                'message' => 'Item added to comparison',
                'data' => $comparisonData,
                'session_id' => $comparison->session_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from comparison
     */
    public function removeItem(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'session_id' => 'nullable|string'
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
            $itemId = $request->get('item_id');
            $sessionId = $request->get('session_id');

            $comparison = Comparison::getOrCreateSession($user ? $user->id : null, $sessionId, $type);
            $comparison->removeItem($itemId);

            $comparisonData = $comparison->getComparisonData();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from comparison',
                'data' => $comparisonData,
                'session_id' => $comparison->session_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear comparison
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'session_id' => 'nullable|string'
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
            $sessionId = $request->get('session_id');

            $comparison = Comparison::getOrCreateSession($user ? $user->id : null, $sessionId, $type);
            $comparison->update(['items' => []]);

            return response()->json([
                'success' => true,
                'message' => 'Comparison cleared',
                'data' => [
                    'items' => [],
                    'attributes' => $comparison->attributes,
                    'type' => $type
                ],
                'session_id' => $comparison->session_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if item is in comparison
     */
    public function checkItem(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'session_id' => 'nullable|string'
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
            $itemId = $request->get('item_id');
            $sessionId = $request->get('session_id');

            $comparison = Comparison::getOrCreateSession($user ? $user->id : null, $sessionId, $type);
            $isInComparison = $comparison->hasItem($itemId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_in_comparison' => $isInComparison,
                    'comparison_count' => count($comparison->items ?? [])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check item status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comparison suggestions
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'current_item_id' => 'required|integer',
                'limit' => 'nullable|integer|min:1|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type');
            $currentItemId = $request->get('current_item_id');
            $limit = $request->get('limit', 10);

            // Get current item
            if ($type === 'product') {
                $currentItem = Product::find($currentItemId);
            } else {
                $currentItem = Business::find($currentItemId);
            }

            if (!$currentItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current item not found'
                ], 404);
            }

            // Get similar items based on category/industry and price/valuation range
            if ($type === 'product') {
                $suggestions = Product::where('id', '!=', $currentItemId)
                    ->where('category', $currentItem->category)
                    ->where('status', 'active')
                    ->whereBetween('price', [
                        $currentItem->price * 0.7,
                        $currentItem->price * 1.3
                    ])
                    ->limit($limit)
                    ->get();
            } else {
                $suggestions = Business::where('id', '!=', $currentItemId)
                    ->where('industry', $currentItem->industry)
                    ->where('status', 'active')
                    ->whereBetween('valuation', [
                        $currentItem->valuation * 0.7,
                        $currentItem->valuation * 1.3
                    ])
                    ->limit($limit)
                    ->get();
            }

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
     * Get user's comparison history
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $comparisons = Comparison::where('user_id', $user->id)
                ->withItems()
                ->active()
                ->orderBy('last_accessed_at', 'desc')
                ->get();

            $history = [];
            foreach ($comparisons as $comparison) {
                $items = $comparison->getItemsData();
                if (!empty($items)) {
                    $history[] = [
                        'id' => $comparison->id,
                        'type' => $comparison->type,
                        'items_count' => count($items),
                        'last_accessed' => $comparison->last_accessed_at,
                        'items' => array_slice($items, 0, 3) // Show first 3 items
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get comparison history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export comparison as PDF/CSV
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:product,business',
                'format' => 'required|in:pdf,csv',
                'session_id' => 'nullable|string'
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
            $format = $request->get('format');
            $sessionId = $request->get('session_id');

            $comparison = Comparison::getOrCreateSession($user ? $user->id : null, $sessionId, $type);
            $comparisonData = $comparison->getComparisonData();

            // For now, return the data structure
            // In a real implementation, you would generate PDF/CSV files
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented',
                'data' => $comparisonData,
                'format' => $format
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 