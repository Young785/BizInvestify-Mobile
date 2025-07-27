<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->get('type'); // 'product' or 'business'
            $perPage = $request->get('per_page', 15);

            $wishlist = Wishlist::with(['wishlistable', 'user'])
                ->where('user_id', $user->id);

            if ($type) {
                $wishlist->ofType($type);
            }

            $wishlist = $wishlist->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $wishlist
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to wishlist
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'notes' => 'nullable|string|max:500',
                'is_public' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $itemType = $request->get('item_type');
            $itemId = $request->get('item_id');
            $notes = $request->get('notes');
            $isPublic = $request->get('is_public', false);

            // Check if item exists
            if ($itemType === 'product') {
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

            // Check if already in wishlist
            if (Wishlist::isInWishlist($user->id, $itemType, $itemId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item already in wishlist'
                ], 400);
            }

            $wishlistItem = Wishlist::addToWishlist(
                $user->id,
                $itemType,
                $itemId,
                $notes,
                $isPublic
            );

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add item to wishlist'
                ], 500);
            }

            $wishlistItem->load(['wishlistable', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Item added to wishlist successfully',
                'data' => $wishlistItem
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from wishlist
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $wishlistItem = Wishlist::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found'
                ], 404);
            }

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update wishlist item (notes, public status)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:500',
                'is_public' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $wishlistItem = Wishlist::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found'
                ], 404);
            }

            $wishlistItem->update([
                'notes' => $request->get('notes'),
                'is_public' => $request->get('is_public', $wishlistItem->is_public)
            ]);

            $wishlistItem->load(['wishlistable', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist item updated successfully',
                'data' => $wishlistItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if item is in user's wishlist
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:product,business',
                'item_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $itemType = $request->get('item_type');
            $itemId = $request->get('item_id');

            $isInWishlist = Wishlist::isInWishlist($user->id, $itemType, $itemId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_in_wishlist' => $isInWishlist
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public wishlists
     */
    public function public(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type'); // 'product' or 'business'
            $limit = $request->get('limit', 20);

            $wishlists = Wishlist::getPublicWishlists($type, $limit);

            return response()->json([
                'success' => true,
                'data' => $wishlists
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve public wishlists',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wishlist statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $stats = [
                'total_items' => Wishlist::where('user_id', $user->id)->count(),
                'products' => Wishlist::where('user_id', $user->id)
                    ->ofType('product')
                    ->count(),
                'businesses' => Wishlist::where('user_id', $user->id)
                    ->ofType('business')
                    ->count(),
                'public_items' => Wishlist::where('user_id', $user->id)
                    ->public()
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wishlist statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 