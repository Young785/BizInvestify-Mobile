<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminWithdrawalController extends Controller
{
    public function __construct(private WithdrawalService $withdrawalService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Withdrawal::with(['user:id,first_name,last_name,email', 'bankAccount'])
                ->orderByDesc('created_at');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('reference', 'like', "%{$search}%");
            }

            $perPage = $request->integer('per_page', 15);
            $withdrawals = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $withdrawals->items(),
                'meta' => [
                    'current_page' => $withdrawals->currentPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                    'last_page' => $withdrawals->lastPage(),
                    'pending_count' => Withdrawal::where('status', 'pending')->count(),
                    'processing_count' => Withdrawal::where('status', 'processing')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Admin list withdrawals failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load withdrawals',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $withdrawal = Withdrawal::with(['user', 'bankAccount'])->find($id);

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $withdrawal]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $withdrawal = Withdrawal::find($id);

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        try {
            $withdrawal = $this->withdrawalService->approve(
                $withdrawal,
                $request->user(),
                $request->input('notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal approved and marked for processing',
                'data' => $withdrawal,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Approve withdrawal failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to approve withdrawal'], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $withdrawal = Withdrawal::find($id);

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        try {
            $withdrawal = $this->withdrawalService->reject(
                $withdrawal,
                $request->user(),
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected',
                'data' => $withdrawal,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Reject withdrawal failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to reject withdrawal'], 500);
        }
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payout_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $withdrawal = Withdrawal::find($id);

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        try {
            $withdrawal = $this->withdrawalService->complete(
                $withdrawal,
                $request->user(),
                $request->payout_reference
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal marked as paid',
                'data' => $withdrawal,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Complete withdrawal failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to complete withdrawal'], 500);
        }
    }
}
