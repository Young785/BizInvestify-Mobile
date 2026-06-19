<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use App\Models\Business;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /**
     * Get user's transactions
     */
    public function getUserTransactions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $type = $request->get('type');

            $query = Transaction::where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });

            if ($status) {
                $query->where('status', $status);
            }

            if ($type === 'purchase') {
                $query->where('buyer_id', $user->id)
                    ->whereIn('type', ['purchase', 'investment']);
            } elseif ($type === 'sale') {
                $query->where('seller_id', $user->id);
            } elseif ($type === 'investment') {
                $query->where('type', 'investment')
                    ->where(function ($q) use ($user) {
                        $q->where('buyer_id', $user->id)->orWhere('user_id', $user->id);
                    });
            } elseif ($type === 'refund') {
                $query->where('type', 'refund')->where('user_id', $user->id);
            } elseif ($type === 'withdrawal') {
                $query->where('type', 'withdrawal')->where('user_id', $user->id);
            }

            $transactions = $query->with(['buyer', 'seller'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add listing details
            foreach ($transactions as $transaction) {
                try {
                    if ($transaction->listing_type === 'product') {
                        $transaction->listing = Product::find($transaction->listing_id);
                    } elseif ($transaction->listing_type === 'business') {
                        $transaction->listing = Business::find($transaction->listing_id);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to load listing for transaction: ' . $transaction->id, [
                        'transaction_id' => $transaction->id,
                        'listing_type' => $transaction->listing_type,
                        'listing_id' => $transaction->listing_id,
                        'error' => $e->getMessage()
                    ]);
                    $transaction->listing = null;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user transactions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions'
            ], 500);
        }
    }

    /**
     * Get specific transaction
     */
    public function getTransaction(Request $request, $transactionId): JsonResponse
    {
        try {
            $user = Auth::user();

            $transaction = Transaction::where('id', $transactionId)
                ->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                      ->orWhere('seller_id', $user->id)
                      ->orWhere('user_id', $user->id);
                })
                ->with(['buyer', 'seller'])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or unauthorized'
                ], 404);
            }

            // Add listing details
            if ($transaction->listing_type === 'product') {
                $transaction->listing = Product::find($transaction->listing_id);
            } elseif ($transaction->listing_type === 'business') {
                $transaction->listing = Business::find($transaction->listing_id);
            }

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction'
            ], 500);
        }
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'seller_id' => 'required|exists:users,id',
                'listing_id' => 'required|integer',
                'listing_type' => 'required|string|in:product,business',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|string|in:card,bank_transfer,wallet',
                'payment_reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Check if user is trying to buy from themselves
            if ($user->id == $request->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot purchase from yourself'
                ], 400);
            }

            // Verify listing exists
            $listing = null;
            if ($request->listing_type === 'product') {
                $listing = Product::where('id', $request->listing_id)
                    ->where('seller_id', $request->seller_id)
                    ->where('status', 'active')
                    ->first();
            } elseif ($request->listing_type === 'business') {
                $listing = Business::where('id', $request->listing_id)
                    ->where('seller_id', $request->seller_id)
                    ->where('status', 'active')
                    ->first();
            }

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or not available'
                ], 404);
            }

            DB::beginTransaction();

            // Calculate commission (5% default)
            $commission = Transaction::calculateCommission($request->amount);
            $netAmount = $request->amount - $commission;

            $transaction = Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $request->seller_id,
                'listing_id' => $request->listing_id,
                'listing_type' => $request->listing_type,
                'amount' => $request->amount,
                'commission' => $commission,
                'net_amount' => $netAmount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'status' => Transaction::STATUS_PENDING,
                'notes' => $request->notes
            ]);

            $transaction->load(['buyer', 'seller']);

            // Create notifications
            Notification::create([
                'user_id' => $request->seller_id,
                'type' => 'transaction',
                'title' => 'New Purchase Order',
                'message' => 'You have a new purchase order for ' . $listing->title,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'buyer_name' => $user->first_name . ' ' . $user->last_name,
                    'amount' => $request->amount,
                    'listing_title' => $listing->title,
                ],
                'priority' => 'high'
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'transaction',
                'title' => 'Purchase Order Created',
                'message' => 'Your purchase order has been created and is pending confirmation',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'listing_title' => $listing->title,
                    'amount' => $request->amount,
                ],
                'priority' => 'medium'
            ]);

            // Log activities
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'transaction_created',
                'description' => 'Created a purchase order for ' . $listing->title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'low'
            ]);

            ActivityLog::create([
                'user_id' => $request->seller_id,
                'activity_type' => 'transaction_received',
                'description' => 'Received a purchase order from ' . $user->first_name . ' ' . $user->last_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'low'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Transaction created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction'
            ], 500);
        }
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(Request $request, $transactionId): JsonResponse
    {
        try {
            $user = Auth::user();

            $transaction = Transaction::where('id', $transactionId)
                ->where('buyer_id', $user->id)
                ->where('status', Transaction::STATUS_PENDING)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found, unauthorized, or cannot be cancelled'
                ], 404);
            }

            DB::beginTransaction();

            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED
            ]);

            // Create notifications
            Notification::create([
                'user_id' => $transaction->seller_id,
                'type' => 'transaction',
                'title' => 'Purchase Order Cancelled',
                'message' => 'A purchase order has been cancelled by the buyer',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'buyer_name' => $user->first_name . ' ' . $user->last_name,
                ],
                'priority' => 'medium'
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'transaction_cancelled',
                'description' => 'Cancelled a purchase order',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'low'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel transaction'
            ], 500);
        }
    }

    /**
     * Export user's transactions
     */
    public function exportTransactions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string',
                'type' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'format' => 'nullable|string|in:csv,json,excel'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Transaction::where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            });

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('type')) {
                if ($request->get('type') === 'purchase') {
                    $query->where('buyer_id', $user->id);
                } elseif ($request->get('type') === 'sale') {
                    $query->where('seller_id', $user->id);
                }
            }

            if ($request->filled('start_date')) {
                $query->where('created_at', '>=', $request->get('start_date'));
            }

            if ($request->filled('end_date')) {
                $query->where('created_at', '<=', $request->get('end_date'));
            }

            $transactions = $query->with(['buyer', 'seller'])
                ->orderBy('created_at', 'desc')
                ->get();

            $format = $request->get('format', 'csv');
            $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.' . $format;
            $disk = Storage::disk('local');
            $path = 'exports/' . $filename;

            if ($format === 'json') {
                $disk->put($path, $transactions->toJson(JSON_PRETTY_PRINT));
            } else {
                $handle = fopen('php://temp', 'r+');
                fputcsv($handle, ['ID', 'Type', 'Amount', 'Status', 'Buyer', 'Seller', 'Created At']);

                foreach ($transactions as $transaction) {
                    fputcsv($handle, [
                        $transaction->id,
                        $transaction->listing_type ?? $transaction->type ?? 'N/A',
                        $transaction->amount,
                        $transaction->status,
                        $transaction->buyer?->email ?? 'N/A',
                        $transaction->seller?->email ?? 'N/A',
                        $transaction->created_at,
                    ]);
                }

                rewind($handle);
                $disk->put($path, stream_get_contents($handle));
                fclose($handle);
            }

            $apiBase = rtrim(config('app.url'), '/') . '/api';

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $apiBase . '/transactions/download/' . $filename,
                    'filename' => $filename,
                    'count' => $transactions->count(),
                    'format' => $format
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export transactions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export transactions'
            ], 500);
        }
    }

    /**
     * Download an exported transactions file.
     */
    public function downloadExport(string $filename): StreamedResponse|JsonResponse
    {
        try {
            if (! preg_match('/^transactions_[\d\-_]+\.(csv|json|excel)$/', $filename)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid export filename',
                ], 400);
            }

            $path = 'exports/' . $filename;

            if (! Storage::disk('local')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export file not found or expired',
                ], 404);
            }

            $mimeType = str_ends_with($filename, '.json') ? 'application/json' : 'text/csv';

            return Storage::disk('local')->download($path, $filename, [
                'Content-Type' => $mimeType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download transaction export: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to download export',
            ], 500);
        }
    }
}