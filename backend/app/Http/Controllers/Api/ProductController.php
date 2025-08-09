<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['seller'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('min_price') && $request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price') && $request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereJsonContains('tags', $searchTerm);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'price', 'title', 'views_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 12);
            $perPage = min($perPage, 50); // Max 50 items per page

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch products', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products'
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
     * Store a newly created product.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'currency' => 'nullable|string|size:3|in:USD,EUR,GBP,CAD,AUD',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
            'inventory_count' => 'required|integer|min:0|max:999999',
            'images' => 'nullable|array|max:5',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120' // 5MB max per image
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
                    'message' => 'Only sellers can create products'
                ], 403);
            }

            // Handle image uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('products/' . $user->id, $filename, 'public');
                    
                    $images[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'order' => $index + 1
                    ];
                }
            }

            $product = Product::create([
                'seller_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'currency' => $request->currency ?? 'USD',
                'category' => $request->category,
                'tags' => $request->tags ?? [],
                'inventory_count' => $request->inventory_count,
                'images' => $images,
                'status' => 'active'
            ]);

            Log::info('Product created successfully', [
                'product_id' => $product->id,
                'seller_id' => $user->id,
                'title' => $product->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'product' => $product->load('seller')
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product creation failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::with(['seller'])
                ->where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Increment view count
            $product->increment('views_count');

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch product', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product'
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
     * Update the specified product.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'currency' => 'sometimes|string|size:3|in:USD,EUR,GBP,CAD,AUD',
            'category' => 'sometimes|string|max:100',
            'tags' => 'sometimes|array|max:10',
            'tags.*' => 'string|max:50',
            'inventory_count' => 'sometimes|integer|min:0|max:999999',
            'status' => 'sometimes|in:active,inactive,sold',
            'images' => 'sometimes|array|max:5',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120'
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
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if user owns this product
            if ($product->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update your own products'
                ], 403);
            }

            $updateData = $request->only([
                'title', 'description', 'price', 'currency', 'category', 
                'tags', 'inventory_count', 'status'
            ]);

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $images = $product->images ?? [];
                
                foreach ($request->file('images') as $index => $image) {
                    $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('products/' . $user->id, $filename, 'public');
                    
                    $images[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'order' => count($images) + 1
                    ];
                }
                
                $updateData['images'] = $images;
            }

            $product->update($updateData);

            Log::info('Product updated successfully', [
                'product_id' => $product->id,
                'seller_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'product' => $product->load('seller')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'product_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product update failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if user owns this product
            if ($product->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own products'
                ], 403);
            }

            // Soft delete by setting status to inactive
            $product->update(['status' => 'inactive']);

            Log::info('Product deleted successfully', [
                'product_id' => $product->id,
                'seller_id' => $user->id,
                'title' => $product->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'product_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product deletion failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'category' => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:relevance,price,created_at,views_count',
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
            
            $query = Product::with(['seller'])
                ->where('status', 'active')
                ->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereJsonContains('tags', $searchTerm);
                });

            // Apply additional filters
            if ($request->category) {
                $query->where('category', $request->category);
            }

            if ($request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            if ($sortBy === 'relevance') {
                // Simple relevance scoring - prioritize title matches
                $query->orderByRaw("CASE WHEN title LIKE '%{$searchTerm}%' THEN 1 ELSE 2 END")
                      ->orderBy('views_count', 'desc');
            } else {
                $query->orderBy($sortBy, 'desc');
            }

            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'search_term' => $searchTerm
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Product search failed', [
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
     * Get products by category.
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        try {
            $query = Product::with(['seller'])
                ->where('status', 'active')
                ->where('category', $category);

            $perPage = $request->get('per_page', 12);
            $products = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'category' => $category
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch products by category', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    /**
     * Get user's own products.
     */
    public function getUserProducts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $query = Product::where('seller_id', $user->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 10);
            $products = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user products', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your products'
            ], 500);
        }
    }

    /**
     * Get trending products.
     */
    public function getTrending(Request $request): JsonResponse
    {
        try {
            $products = Product::with(['seller'])
                ->where('status', 'active')
                ->orderBy('views_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch trending products', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending products'
            ], 500);
        }
    }

    /**
     * Get related products.
     */
    public function getRelated(Request $request, string $productId): JsonResponse
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $relatedProducts = Product::with(['seller'])
                ->where('id', '!=', $productId)
                ->where('status', 'active')
                ->where(function($query) use ($product) {
                    $query->where('category', $product->category)
                          ->orWhere('seller_id', $product->seller_id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $relatedProducts
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch related products', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch related products'
            ], 500);
        }
    }

    /**
     * Display a public listing of products (no authentication required).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['seller'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('min_price') && $request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price') && $request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhereJsonContains('tags', $searchTerm);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'price', 'title', 'views_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 12);
            $perPage = min($perPage, 50); // Max 50 items per page

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch public products', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    /**
     * Display a public product (no authentication required).
     */
    public function publicShow(string $id): JsonResponse
    {
        try {
            $product = Product::with(['seller'])
                ->where('status', 'active')
                ->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Increment view count
            $product->increment('views_count');

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch public product', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product'
            ], 500);
        }
    }
}
