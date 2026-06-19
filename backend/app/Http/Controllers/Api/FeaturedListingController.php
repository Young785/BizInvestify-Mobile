<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedListing;
use App\Models\Product;
use App\Models\Business;
use App\Services\FeaturedListingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FeaturedListingController extends Controller
{
    public function __construct(private FeaturedListingService $featuredListingService)
    {
    }

    private function parseTargeting(Request $request): ?array
    {
        $targeting = $request->get('targeting');
        if (is_string($targeting)) {
            $decoded = json_decode($targeting, true);
            return is_array($decoded) ? $decoded : null;
        }

        return is_array($targeting) ? $targeting : null;
    }

    private function findOwnedListable(string $type, int $id, $user)
    {
        if ($type === 'product') {
            return Product::where('id', $id)->where('seller_id', $user->id)->first();
        }

        return Business::where('id', $id)->where('seller_id', $user->id)->first();
    }
    /**
     * Get featured listings for display
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:product,business',
                'promotion_type' => 'nullable|in:featured,sponsored,trending,editor_choice',
                'limit' => 'nullable|integer|min:1|max:50',
                'user_id' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type');
            $promotionType = $request->get('promotion_type');
            $limit = $request->get('limit', 10);
            $userId = $request->get('user_id');

            $query = FeaturedListing::active()
                ->withinBudget()
                ->with(['listable', 'user']);

            if ($type) {
                $query->byListableType($type);
            }

            if ($promotionType) {
                $query->byType($promotionType);
            }

            // Apply user targeting if user_id provided
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $userCriteria = [
                        'location' => $user->city,
                        'user_type' => $user->role,
                        'category' => null
                    ];

                    $listings = $query->get()->filter(function ($listing) use ($userCriteria) {
                        return $listing->meetsTargeting($userCriteria);
                    })->take($limit);
                } else {
                    $listings = $query->take($limit)->get();
                }
            } else {
                $listings = $query->take($limit)->get();
            }

            // Record impressions for displayed listings
            foreach ($listings as $listing) {
                $listing->recordImpression();
            }

            return response()->json([
                'success' => true,
                'data' => $listings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new featured listing
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'listable_type' => 'required|in:product,business',
                'listable_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'promotion_type' => 'required|in:featured,sponsored,trending,editor_choice',
                'daily_budget' => 'required|numeric|min:1',
                'total_budget' => 'required|numeric|min:1',
                'transaction_id' => 'required|integer|exists:transactions,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'targeting' => 'nullable|array',
                'targeting.locations' => 'nullable|array',
                'targeting.categories' => 'nullable|array',
                'targeting.user_types' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $listableType = $request->get('listable_type');
            $listableId = $request->get('listable_id');

            // Check if item exists and belongs to user
            $item = $this->findOwnedListable($listableType, $listableId, $user);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found or does not belong to you'
                ], 404);
            }

            // Check if user already has an active promotion for this item
            $existingPromotion = FeaturedListing::where('user_id', $user->id)
                ->where('listable_type', $listableType)
                ->where('listable_id', $listableId)
                ->whereIn('status', ['active', 'pending'])
                ->first();

            if ($existingPromotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active promotion for this item'
                ], 400);
            }

            $transaction = \App\Models\Transaction::where('id', $request->get('transaction_id'))
                ->where('user_id', $user->id)
                ->where('type', 'featured_listing')
                ->where('status', 'completed')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'A completed payment is required before creating a featured listing'
                ], 402);
            }

            if (\App\Models\FeaturedListing::where('transaction_id', $transaction->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment has already been used for a featured listing'
                ], 400);
            }

            if ((float) $transaction->amount < (float) $request->get('total_budget')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount must cover the total promotion budget'
                ], 400);
            }

            // Handle banner image upload
            $bannerImage = null;
            if ($request->hasFile('banner_image')) {
                $path = $request->file('banner_image')->store('featured-banners', 'public');
                $bannerImage = $path;
            }

            $featuredListing = FeaturedListing::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'listable_type' => $listableType,
                'listable_id' => $listableId,
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'banner_image' => $bannerImage ? [$bannerImage] : null,
                'promotion_type' => $request->get('promotion_type'),
                'daily_budget' => $request->get('daily_budget'),
                'total_budget' => $request->get('total_budget'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'targeting' => $this->parseTargeting($request),
                'status' => 'pending' // Requires admin approval
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Featured listing created successfully and pending approval',
                'data' => $featuredListing->load(['listable', 'user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create featured listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's featured listings
     */
    public function myListings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $status = $request->get('status');
            $type = $request->get('type');

            $query = FeaturedListing::where('user_id', $user->id)
                ->with(['listable', 'user']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->byListableType($type);
            }

            $listings = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $listings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your featured listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured listing details
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $featuredListing = FeaturedListing::with(['listable', 'user', 'approvedBy'])
                ->findOrFail($id);

            // Check if user owns this listing or is admin
            if ($featuredListing->user_id !== $user->id && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Record click if accessed by someone else
            if ($featuredListing->user_id !== $user->id) {
                $featuredListing->recordClick();
            }

            return response()->json([
                'success' => true,
                'data' => $featuredListing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update featured listing
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'daily_budget' => 'nullable|numeric|min:1',
                'total_budget' => 'nullable|numeric|min:1',
                'end_date' => 'nullable|date|after:today',
                'targeting' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $featuredListing = FeaturedListing::findOrFail($id);

            // Check if user owns this listing
            if ($featuredListing->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                // Delete old banner image
                if ($featuredListing->banner_image) {
                    foreach ($featuredListing->banner_image as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }

                $path = $request->file('banner_image')->store('featured-banners', 'public');
                $featuredListing->banner_image = [$path];
            }

            $featuredListing->update($request->only([
                'title', 'description', 'daily_budget', 'total_budget', 
                'end_date', 'targeting'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Featured listing updated successfully',
                'data' => $featuredListing->load(['listable', 'user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause or resume promotion
     */
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $featuredListing = FeaturedListing::findOrFail($id);

            // Check if user owns this listing
            if ($featuredListing->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($featuredListing->status === 'active') {
                $featuredListing->pause();
                $message = 'Promotion paused successfully';
            } else {
                $featuredListing->resume();
                $message = 'Promotion resumed successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $featuredListing->load(['listable', 'user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update promotion status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete featured listing
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $featuredListing = FeaturedListing::findOrFail($id);

            // Check if user owns this listing
            if ($featuredListing->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Delete banner image
            if ($featuredListing->banner_image) {
                foreach ($featuredListing->banner_image as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $featuredListing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Featured listing deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete featured listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get promotion performance analytics
     */
    public function analytics(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $featuredListing = FeaturedListing::findOrFail($id);

            // Check if user owns this listing
            if ($featuredListing->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $performance = $featuredListing->getPerformanceSummary();

            return response()->json([
                'success' => true,
                'data' => $performance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get promotion statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Only admins can view global statistics
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $stats = FeaturedListing::getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a click on a featured listing
     */
    public function recordClick(Request $request, $id): JsonResponse
    {
        try {
            $featuredListing = FeaturedListing::findOrFail($id);
            
            if ($featuredListing->isActive()) {
                $featuredListing->recordClick();
            }

            return response()->json([
                'success' => true,
                'message' => 'Click recorded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record click',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: approve pending featured listing
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $featuredListing = FeaturedListing::findOrFail($id);
            $listing = $this->featuredListingService->approve($featuredListing, $user);

            return response()->json([
                'success' => true,
                'message' => 'Featured listing approved',
                'data' => $listing,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: reject pending featured listing
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $featuredListing = FeaturedListing::findOrFail($id);
            $listing = $this->featuredListingService->reject($featuredListing, $user, $validated['reason']);

            return response()->json([
                'success' => true,
                'message' => 'Featured listing rejected and refund initiated when applicable',
                'data' => $listing,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: list pending featured listings
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user->hasRole(['admin', 'super_admin'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $listings = FeaturedListing::where('status', 'pending')
                ->with(['listable', 'user', 'transaction'])
                ->orderByDesc('created_at')
                ->paginate(20);

            return response()->json(['success' => true, 'data' => $listings]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
} 