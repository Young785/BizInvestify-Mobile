<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(private OrderFulfillmentService $fulfillmentService)
    {
    }

    private function authorizeSeller(Order $order): ?JsonResponse
    {
        $sellerId = $order->seller_id ?? $order->user_id;

        if ((int) $sellerId !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Order::forUser($user->id)->with(['product', 'statusHistories']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->byStatus($request->status);
            }

            if ($request->has('search') && ! empty($request->search)) {
                $query->search($request->search);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->integer('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get orders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders',
            ], 500);
        }
    }

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
                'notes' => 'nullable|string|max:1000',
                'shipping_address' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $product = Product::find($request->product_id);

            if (! $product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'seller_id' => $product->seller_id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'product_id' => $product->id,
                'product_name' => $product->title,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'USD',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'shipping_address' => $request->shipping_address,
                'status' => 'pending',
            ]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'pending',
                'notes' => 'Order created',
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['product', 'statusHistories']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create order: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to create order'], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        if ($response = $this->authorizeSeller($order)) {
            return $response;
        }

        return response()->json([
            'success' => true,
            'data' => $order->load(['product', 'statusHistories.changedBy:id,first_name,last_name']),
        ]);
    }

    public function tracking(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'shipping_carrier' => $order->shipping_carrier,
                'tracking_number' => $order->tracking_number,
                'shipped_at' => $order->shipped_at,
                'delivered_at' => $order->delivered_at,
                'timeline' => $order->statusHistories()
                    ->orderBy('created_at')
                    ->get(['from_status', 'to_status', 'notes', 'created_at'])
                    ->map(fn ($entry) => [
                        'status' => $entry->to_status,
                        'previous_status' => $entry->from_status,
                        'from_status' => $entry->from_status,
                        'to_status' => $entry->to_status,
                        'notes' => $entry->notes,
                        'created_at' => $entry->created_at,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        if ($response = $this->authorizeSeller($order)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order->update($request->only(['customer_name', 'customer_email', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load(['product', 'statusHistories']),
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($response = $this->authorizeSeller($order)) {
            return $response;
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be deleted',
            ], 400);
        }

        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order deleted successfully']);
    }

    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Order::forUser($user->id);

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            $base = clone $query;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => (clone $base)->count(),
                    'pending_orders' => (clone $base)->byStatus('pending')->count(),
                    'confirmed_orders' => (clone $base)->byStatus('confirmed')->count(),
                    'processing_orders' => (clone $base)->byStatus('processing')->count(),
                    'shipped_orders' => (clone $base)->byStatus('shipped')->count(),
                    'delivered_orders' => (clone $base)->byStatus('delivered')->count(),
                    'completed_orders' => (clone $base)->whereIn('status', ['completed', 'delivered'])->count(),
                    'cancelled_orders' => (clone $base)->byStatus('cancelled')->count(),
                    'total_revenue' => round((clone $base)->whereIn('status', ['completed', 'delivered'])->sum('amount'), 2),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order stats: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to load order statistics'], 500);
        }
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        if ($response = $this->authorizeSeller($order)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:'.implode(',', OrderFulfillmentService::STATUSES),
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
        }

        try {
            $order = $this->fulfillmentService->updateStatus(
                $order,
                $request->status,
                $request->user(),
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update order status: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to update order status'], 500);
        }
    }

    public function updateShipping(Request $request, Order $order): JsonResponse
    {
        if ($response = $this->authorizeSeller($order)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'shipping_carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string|max:1000',
            'mark_shipped' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = $this->fulfillmentService->updateShipping(
                $order,
                $request->user(),
                $request->shipping_carrier,
                $request->tracking_number,
                $request->shipping_address,
                $request->boolean('mark_shipped')
            );

            return response()->json([
                'success' => true,
                'message' => 'Shipping details updated',
                'data' => $order,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update shipping: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to update shipping'], 500);
        }
    }
}
