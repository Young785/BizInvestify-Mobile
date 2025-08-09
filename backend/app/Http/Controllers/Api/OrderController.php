<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Get user's orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Order::forUser($user->id)->with(['product']);
            
            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->byStatus($request->status);
            }
            
            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }
            
            // Date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                "error" => $e->getMessage(),
                'message' => 'Failed to load orders'
            ], 500);
        }
    }

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'product_id' => 'required|exists:products,id',
                'amount' => 'required|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'payment_method' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $product = Product::find($request->product_id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'product_id' => $product->id,
                'product_name' => $product->title,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'USD',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['product'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order'
            ], 500);
        }
    }

    /**
     * Get a specific order
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns this order
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $order->load(['product'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load order'
            ], 500);
        }
    }

    /**
     * Update an order
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns this order
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'customer_name' => 'sometimes|string|max:255',
                'customer_email' => 'sometimes|email|max:255',
                'status' => 'sometimes|in:pending,processing,completed,cancelled,refunded',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order->update($request->only([
                'customer_name',
                'customer_email', 
                'status',
                'notes'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->load(['product'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order'
            ], 500);
        }
    }

    /**
     * Delete an order
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns this order
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Only allow deletion of pending orders
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be deleted'
                ], 400);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order'
            ], 500);
        }
    }

    /**
     * Get order statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Order::forUser($user->id);
            
            // Apply date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }
            
            $totalOrders = $query->count();
            $pendingOrders = (clone $query)->byStatus('pending')->count();
            $processingOrders = (clone $query)->byStatus('processing')->count();
            $completedOrders = (clone $query)->byStatus('completed')->count();
            $cancelledOrders = (clone $query)->byStatus('cancelled')->count();
            $totalRevenue = (clone $query)->byStatus('completed')->sum('amount');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'processing_orders' => $processingOrders,
                    'completed_orders' => $completedOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'total_revenue' => round($totalRevenue, 2)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to load order statistics'
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns this order
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,processing,completed,cancelled,refunded'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 422);
            }

            $order->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->load(['product'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status'
            ], 500);
        }
    }
} 