<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    /**
     * Display a listing of businesses.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Business::with(['seller', 'investments'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('industry') && $request->industry) {
                $query->where('industry', $request->industry);
            }

            if ($request->has('min_valuation') && $request->min_valuation) {
                $query->where('valuation', '>=', $request->min_valuation);
            }

            if ($request->has('max_valuation') && $request->max_valuation) {
                $query->where('valuation', '<=', $request->max_valuation);
            }

            if ($request->has('min_funding') && $request->min_funding) {
                $query->where('funding_goal', '>=', $request->min_funding);
            }

            if ($request->has('max_funding') && $request->max_funding) {
                $query->where('funding_goal', '<=', $request->max_funding);
            }

            if ($request->has('location') && $request->location) {
                $query->where('location', 'LIKE', "%{$request->location}%");
            }

            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('industry', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'valuation', 'funding_goal', 'name', 'views_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 12);
            $perPage = min($perPage, 50);

            $businesses = $query->paginate($perPage);

            // Calculate funding progress for each business
            $data = $businesses->items();
            foreach ($data as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
                    'from' => $businesses->firstItem(),
                    'to' => $businesses->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch businesses', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch businesses'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created business.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'industry' => 'required|string|max:100',
            'valuation' => 'required|numeric|min:1|max:999999999.99',
            'funding_goal' => 'required|numeric|min:1|max:999999999.99',
            'equity_offered' => 'required|numeric|min:0.01|max:100',
            'revenue' => 'nullable|numeric|min:0|max:999999999.99',
            'profit_margin' => 'nullable|numeric|min:-100|max:100',
            'employees_count' => 'nullable|integer|min:1|max:99999',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'location' => 'required|string|max:255',
            'business_plan' => 'nullable|string|max:10000',
            'pitch_deck' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240', // 10MB max
            'business_images' => 'nullable|array|max:10',
            'business_images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            
            // Check if user is a seller
            if (!$user->isSeller()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only sellers can create business listings'
                ], 403);
            }

            // Handle pitch deck upload
            $pitchDeckUrl = null;
            if ($request->hasFile('pitch_deck')) {
                $pitchDeck = $request->file('pitch_deck');
                $filename = Str::random(40) . '.' . $pitchDeck->getClientOriginalExtension();
                $path = $pitchDeck->storeAs('businesses/' . $user->id . '/documents', $filename, 'public');
                $pitchDeckUrl = Storage::url($path);
            }

            // Handle business images upload
            $images = [];
            if ($request->hasFile('business_images')) {
                foreach ($request->file('business_images') as $index => $image) {
                    $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('businesses/' . $user->id . '/images', $filename, 'public');
                    
                    $images[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'order' => $index + 1
                    ];
                }
            }

            $business = Business::create([
                'seller_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'industry' => $request->industry,
                'valuation' => $request->valuation,
                'funding_goal' => $request->funding_goal,
                'equity_offered' => $request->equity_offered,
                'pitch_deck_url' => $pitchDeckUrl,
                'business_plan' => $request->business_plan,
                'revenue' => $request->revenue,
                'profit_margin' => $request->profit_margin,
                'employees_count' => $request->employees_count,
                'founded_year' => $request->founded_year,
                'location' => $request->location,
                'business_images' => $images,
                'status' => 'active'
            ]);

            Log::info('Business created successfully', [
                'business_id' => $business->id,
                'seller_id' => $user->id,
                'name' => $business->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business listing created successfully',
                'data' => [
                    'business' => $business->load('seller')
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Business creation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Business listing creation failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified business.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $business = Business::with(['seller', 'investments' => function($query) {
                $query->where('status', 'completed')->with('investor');
            }])
                ->where('status', 'active')
                ->where(function ($query) use ($id) {
                    $query->where('slug', $id);

                    if (ctype_digit($id)) {
                        $query->orWhere('id', (int) $id);
                    }
                })
                ->first();

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            // Increment view count
            $business->increment('views_count');

            // Calculate investment stats
            $totalInvested = $business->investments->sum('amount');
            $business->funding_progress = $business->funding_goal > 0 
                ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                : 0;
            $business->total_invested = $totalInvested;
            $business->investors_count = $business->investments->count();
            $business->remaining_funding = max(0, $business->funding_goal - $totalInvested);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'business' => $business
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch business', [
                'business_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business'
            ], 500);
        }
    }

    /**
     * Display business for owner editing (any status, slug or id).
     */
    public function showForOwner(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $business = Business::with(['seller', 'investments'])->findBySlugOrId($id);

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found',
                ], 404);
            }

            if ($business->seller_id !== $user->id && ! $user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own business listings',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $business,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch business for owner', [
                'business_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified business.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'industry' => 'sometimes|string|max:100',
            'valuation' => 'sometimes|numeric|min:1|max:999999999.99',
            'funding_goal' => 'sometimes|numeric|min:1|max:999999999.99',
            'equity_offered' => 'sometimes|numeric|min:0.01|max:100',
            'revenue' => 'sometimes|numeric|min:0|max:999999999.99',
            'profit_margin' => 'sometimes|numeric|min:-100|max:100',
            'employees_count' => 'sometimes|integer|min:1|max:99999',
            'founded_year' => 'sometimes|integer|min:1800|max:' . date('Y'),
            'location' => 'sometimes|string|max:255',
            'business_plan' => 'sometimes|string|max:10000',
            'status' => 'sometimes|in:active,inactive,funded,sold',
            'pitch_deck' => 'sometimes|file|mimes:pdf,ppt,pptx|max:10240',
            'business_images' => 'sometimes|array|max:10',
            'business_images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $business = Business::findBySlugOrId($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            // Check if user owns this business
            if ($business->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update your own business listings'
                ], 403);
            }

            $updateData = $request->only([
                'name', 'description', 'industry', 'valuation', 'funding_goal', 
                'equity_offered', 'revenue', 'profit_margin', 'employees_count', 
                'founded_year', 'location', 'business_plan', 'status'
            ]);

            // Handle pitch deck update
            if ($request->hasFile('pitch_deck')) {
                $pitchDeck = $request->file('pitch_deck');
                $filename = Str::random(40) . '.' . $pitchDeck->getClientOriginalExtension();
                $path = $pitchDeck->storeAs('businesses/' . $user->id . '/documents', $filename, 'public');
                $updateData['pitch_deck_url'] = Storage::url($path);
            }

            // Handle business images update
            if ($request->hasFile('business_images')) {
                $images = $business->business_images ?? [];
                
                foreach ($request->file('business_images') as $index => $image) {
                    $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('businesses/' . $user->id . '/images', $filename, 'public');
                    
                    $images[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'order' => count($images) + 1
                    ];
                }
                
                $updateData['business_images'] = $images;
            }

            $business->update($updateData);

            Log::info('Business updated successfully', [
                'business_id' => $business->id,
                'seller_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully',
                'data' => [
                    'business' => $business->load('seller')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Business update failed', [
                'business_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Business update failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified business.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $business = Business::findBySlugOrId($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            // Check if user owns this business
            if ($business->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own business listings'
                ], 403);
            }

            // Soft delete by setting status to inactive
            $business->update(['status' => 'inactive']);

            Log::info('Business deleted successfully', [
                'business_id' => $business->id,
                'seller_id' => $user->id,
                'name' => $business->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business listing deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Business deletion failed', [
                'business_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Business deletion failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search businesses.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'industry' => 'nullable|string|max:100',
            'min_valuation' => 'nullable|numeric|min:0',
            'max_valuation' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:relevance,valuation,funding_goal,created_at,views_count',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $searchTerm = $request->q;
            
            $query = Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('industry', 'LIKE', "%{$searchTerm}%");
                });

            // Apply additional filters
            if ($request->industry) {
                $query->where('industry', $request->industry);
            }

            if ($request->min_valuation) {
                $query->where('valuation', '>=', $request->min_valuation);
            }

            if ($request->max_valuation) {
                $query->where('valuation', '<=', $request->max_valuation);
            }

            if ($request->location) {
                $query->where('location', 'LIKE', "%{$request->location}%");
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            if ($sortBy === 'relevance') {
                $query->orderByRaw("CASE WHEN name LIKE '%{$searchTerm}%' THEN 1 ELSE 2 END")
                      ->orderBy('views_count', 'desc');
            } else {
                $query->orderBy($sortBy, 'desc');
            }

            $perPage = $request->get('per_page', 12);
            $businesses = $query->paginate($perPage);

            // Calculate funding progress for each business
            $data = $businesses->items();
            foreach ($data as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
                    'search_term' => $searchTerm
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Business search failed', [
                'search_term' => $request->q,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get businesses by industry.
     */
    public function byIndustry(Request $request, string $industry): JsonResponse
    {
        try {
            $query = Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->where('industry', $industry);

            $perPage = $request->get('per_page', 12);
            $businesses = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage);

            // Calculate funding progress for each business
            $data = $businesses->items();
            foreach ($data as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
                    'industry' => $industry
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch businesses by industry', [
                'industry' => $industry,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch businesses'
            ], 500);
        }
    }

    /**
     * Get user's own businesses.
     */
    public function getUserBusinesses(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $query = Business::with(['investments'])
                ->where('seller_id', $user->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 10);
            $businesses = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage);

            // Calculate investment stats for each business
            $data = $businesses->items();
            foreach ($data as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
                $business->pending_investments = $business->investments->where('status', 'pending')->count();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user businesses', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your business listings'
            ], 500);
        }
    }

    /**
     * Get featured businesses.
     */
    public function getFeatured(Request $request): JsonResponse
    {
        try {
            $businesses = Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->where('featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $businesses
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch featured businesses', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured businesses'
            ], 500);
        }
    }

    /**
     * Get trending businesses.
     */
    public function getTrending(Request $request): JsonResponse
    {
        try {
            $businesses = Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $businesses
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch trending businesses', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending businesses'
            ], 500);
        }
    }

    /**
     * Get related businesses.
     */
    public function getRelated(Request $request, string $businessId): JsonResponse
    {
        try {
            $business = Business::findBySlugOrId($businessId);
            
            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            $relatedBusinesses = Business::with(['seller', 'investments'])
                ->where('id', '!=', $business->id)
                ->where('status', 'active')
                ->where(function($query) use ($business) {
                    $query->where('industry', $business->industry)
                          ->orWhere('location', $business->location);
                })
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $relatedBusinesses
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch related businesses', [
                'business_id' => $businessId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch related businesses'
            ], 500);
        }
    }

    /**
     * Display a public listing of businesses (no authentication required).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $query = Business::with(['seller', 'investments'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('industry') && $request->industry) {
                $query->where('industry', $request->industry);
            }

            if ($request->has('min_valuation') && $request->min_valuation) {
                $query->where('valuation', '>=', $request->min_valuation);
            }

            if ($request->has('max_valuation') && $request->max_valuation) {
                $query->where('valuation', '<=', $request->max_valuation);
            }

            if ($request->has('min_funding') && $request->min_funding) {
                $query->where('funding_goal', '>=', $request->min_funding);
            }

            if ($request->has('max_funding') && $request->max_funding) {
                $query->where('funding_goal', '<=', $request->max_funding);
            }

            if ($request->has('location') && $request->location) {
                $query->where('location', 'LIKE', "%{$request->location}%");
            }

            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('industry', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'valuation', 'funding_goal', 'name', 'views_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 12);
            $perPage = min($perPage, 50);

            $businesses = $query->paginate($perPage);

            // Calculate funding progress for each business
            $data = $businesses->items();
            foreach ($data as $business) {
                $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
                $business->funding_progress = $business->funding_goal > 0 
                    ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                    : 0;
                $business->total_invested = $totalInvested;
                $business->investors_count = $business->investments->where('status', 'completed')->count();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
                    'from' => $businesses->firstItem(),
                    'to' => $businesses->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch public businesses', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch businesses'
            ], 500);
        }
    }

    /**
     * Display a public business (no authentication required).
     */
    public function publicShow(string $id): JsonResponse
    {
        try {
            $business = Business::with(['seller', 'investments'])
                ->where('status', 'active')
                ->where(function ($query) use ($id) {
                    $query->where('slug', $id);

                    if (ctype_digit($id)) {
                        $query->orWhere('id', (int) $id);
                    }
                })
                ->first();

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            // Increment view count
            $business->increment('views_count');

            // Calculate funding progress
            $totalInvested = $business->investments->where('status', 'completed')->sum('amount');
            $business->funding_progress = $business->funding_goal > 0 
                ? round(($totalInvested / $business->funding_goal) * 100, 2) 
                : 0;
            $business->total_invested = $totalInvested;
            $business->investors_count = $business->investments->where('status', 'completed')->count();

            return response()->json([
                'success' => true,
                'data' => $business
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch public business', [
                'business_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business'
            ], 500);
        }
    }
}
