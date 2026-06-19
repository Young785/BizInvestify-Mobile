<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = ProductCategory::query()
                ->where(function ($q) use ($user) {
                    $q->whereNull('seller_id')
                        ->orWhere('seller_id', $user->id);
                })
                ->orderBy('name');

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            $categories = $query->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list categories: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories',
            ], 500);
        }
    }

    public function show(string $category): JsonResponse
    {
        try {
            $record = ProductCategory::findBySlugOrId($category);

            if (! $record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get category: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load category',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            $category = ProductCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->get('status', 'active'),
                'seller_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
            ], 500);
        }
    }

    public function update(Request $request, string $category): JsonResponse
    {
        $record = ProductCategory::findBySlugOrId($category);

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $oldName = $record->name;
            $record->fill($request->only(['name', 'description', 'status']));
            $record->save();

            if ($record->wasChanged('name')) {
                Product::query()
                    ->where('category', $oldName)
                    ->update(['category' => $record->name]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $record->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update category: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
            ], 500);
        }
    }

    public function destroy(string $category): JsonResponse
    {
        $record = ProductCategory::findBySlugOrId($category);

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        try {
            $productCount = Product::query()
                ->where('category', $record->name)
                ->count();

            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing products',
                ], 422);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete category: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
            ], 500);
        }
    }
}
