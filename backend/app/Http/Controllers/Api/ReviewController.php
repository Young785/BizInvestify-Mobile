<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Get reviews for an item
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'rating' => 'nullable|integer|between:1,5',
                'sort_by' => 'nullable|in:recent,helpful,rating',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $itemType = $request->get('item_type');
            $itemId = $request->get('item_id');
            $rating = $request->get('rating');
            $sortBy = $request->get('sort_by', 'recent');
            $perPage = $request->get('per_page', 10);

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

            $query = Review::with(['user'])
                ->where('reviewable_type', $itemType)
                ->where('reviewable_id', $itemId)
                ->approved();

            // Filter by rating
            if ($rating) {
                $query->byRating($rating);
            }

            // Sort reviews
            switch ($sortBy) {
                case 'helpful':
                    $query->helpful();
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $reviews = $query->paginate($perPage);

            // Get review statistics
            $stats = [
                'average_rating' => Review::getAverageRating($itemType, $itemId),
                'total_reviews' => Review::getReviewCount($itemType, $itemId),
                'verified_purchases' => Review::getVerifiedPurchaseCount($itemType, $itemId),
                'rating_distribution' => Review::getRatingDistribution($itemType, $itemId)
            ];

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new review
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:product,business',
                'item_id' => 'required|integer',
                'rating' => 'required|integer|between:1,5',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|min:10|max:2000',
                'images' => 'nullable|array|max:5',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            // Check if user can review this item
            if (!Review::canReview($user->id, $itemType, $itemId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot review this item. You must purchase/invest in it first and not have already reviewed it.'
                ], 403);
            }

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

            // Handle image uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');
                    $images[] = $path;
                }
            }

            // Check if user has verified purchase/investment
            $isVerifiedPurchase = false;
            if ($itemType === 'product') {
                $isVerifiedPurchase = Transaction::where('user_id', $user->id)
                    ->where('type', 'purchase')
                    ->where('reference_id', $itemId)
                    ->where('status', 'completed')
                    ->exists();
            } else {
                $isVerifiedPurchase = Investment::where('investor_id', $user->id)
                    ->where('business_id', $itemId)
                    ->where('status', 'completed')
                    ->exists();
            }

            $review = Review::create([
                'user_id' => $user->id,
                'reviewable_type' => $itemType,
                'reviewable_id' => $itemId,
                'rating' => $request->get('rating'),
                'title' => $request->get('title'),
                'content' => $request->get('content'),
                'images' => $images,
                'is_verified_purchase' => $isVerifiedPurchase
            ]);

            $review->load(['user']);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'nullable|integer|between:1,5',
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string|min:10|max:2000',
                'images' => 'nullable|array|max:5',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $review = Review::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found or you do not have permission to edit it'
                ], 404);
            }

            // Handle image uploads
            $images = $review->images ?? [];
            if ($request->hasFile('images')) {
                // Delete old images
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
                
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');
                    $images[] = $path;
                }
            }

            $review->update([
                'rating' => $request->get('rating', $review->rating),
                'title' => $request->get('title', $review->title),
                'content' => $request->get('content', $review->content),
                'images' => $images
            ]);

            $review->load(['user']);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $review = Review::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found or you do not have permission to delete it'
                ], 404);
            }

            // Delete associated images
            if ($review->images) {
                foreach ($review->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            // Check if user already marked this review as helpful
            // You might want to create a separate table for tracking helpful votes
            $review->markAsHelpful();

            return response()->json([
                'success' => true,
                'message' => 'Review marked as helpful',
                'data' => [
                    'helpful_count' => $review->helpful_count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark review as helpful',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can review an item
     */
    public function canReview(Request $request): JsonResponse
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

            $canReview = Review::canReview($user->id, $itemType, $itemId);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_review' => $canReview
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check review eligibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 10);

            $reviews = Review::with(['reviewable'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 