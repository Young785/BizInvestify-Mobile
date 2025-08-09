<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    /**
     * Display a listing of investments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Investment::with(['business', 'investor']);

            // Filter by user role and permissions
            if ($request->has('as_business_owner') && $request->as_business_owner) {
                // Business owner viewing investments in their businesses
                $businessIds = $user->businesses()->pluck('id');
                $query->whereIn('business_id', $businessIds);
            } else {
                // Investor viewing their own investments
                $query->where('investor_id', $user->id);
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'amount', 'equity_percentage', 'status'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $perPage = min($perPage, 50);

            if ($request->get('include_history') || $request->get('portfolio')) {
                $investments = $query->paginate($perPage);
                
                return response()->json([
                    'success' => true,
                    'data' => $investments->items(),
                    'meta' => [
                        'current_page' => $investments->currentPage(),
                        'per_page' => $investments->perPage(),
                        'total' => $investments->total(),
                        'last_page' => $investments->lastPage()
                    ]
                ]);
            } else {
                // Return all for dashboard
                $investments = $query->get();
                return response()->json($investments);
            }

        } catch (\Exception $e) {
            Log::error('Failed to fetch investments', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch investments'
            ], 500);
        }
    }

    /**
     * Store a newly created investment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|uuid|exists:businesses,id',
            'amount' => 'required|numeric|min:100|max:999999999.99',
            'message' => 'nullable|string|max:1000'
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
            
            // Check if user is a buyer/investor
            if (!$user->isBuyer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only buyers/investors can make investments'
                ], 403);
            }

            $business = Business::find($request->business_id);
            
            if (!$business || $business->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not available for investment'
                ], 404);
            }

            // Check if user is trying to invest in their own business
            if ($business->seller_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot invest in your own business'
                ], 403);
            }

            // Calculate equity percentage based on business valuation
            $equityPercentage = ($request->amount / $business->valuation) * 100;
            
            // Check if this exceeds available equity
            $totalEquityOffered = Investment::where('business_id', $business->id)
                ->where('status', 'completed')
                ->sum('equity_percentage');
                
            if (($totalEquityOffered + $equityPercentage) > $business->equity_offered) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment would exceed available equity. Maximum available: ' . 
                                ($business->equity_offered - $totalEquityOffered) . '%'
                ], 422);
            }

            DB::beginTransaction();

            try {
                $investment = Investment::create([
                    'investor_id' => $user->id,
                    'business_id' => $request->business_id,
                    'amount' => $request->amount,
                    'equity_percentage' => round($equityPercentage, 4),
                    'status' => 'pending',
                    'message' => $request->message
                ]);

                DB::commit();

                Log::info('Investment created successfully', [
                    'investment_id' => $investment->id,
                    'investor_id' => $user->id,
                    'business_id' => $request->business_id,
                    'amount' => $request->amount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Investment submitted successfully',
                    'data' => [
                        'investment' => $investment->load(['business', 'investor'])
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Investment creation failed', [
                'user_id' => $request->user()->id,
                'business_id' => $request->business_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Investment creation failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified investment.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $investment = Investment::with(['business', 'investor'])
                ->where('id', $id)
                ->first();

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            // Check if user has permission to view this investment
            $canView = ($investment->investor_id === $user->id) || 
                      ($investment->business->seller_id === $user->id) ||
                      ($user->isAdmin());

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this investment'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'investment' => $investment
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch investment', [
                'investment_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch investment'
            ], 500);
        }
    }

    /**
     * Update the specified investment.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:100|max:999999999.99',
            'message' => 'sometimes|string|max:1000',
            'status' => 'sometimes|in:pending,approved,rejected,completed'
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
            $investment = Investment::with(['business'])->find($id);

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            // Check permissions
            $canUpdate = false;
            
            if ($investment->investor_id === $user->id && $investment->status === 'pending') {
                // Investor can update their pending investment
                $canUpdate = true;
                $updateData = $request->only(['amount', 'message']);
            } elseif ($investment->business->seller_id === $user->id) {
                // Business owner can update status
                $canUpdate = true;
                $updateData = $request->only(['status']);
            } elseif ($user->isAdmin()) {
                // Admin can update everything
                $canUpdate = true;
                $updateData = $request->only(['amount', 'message', 'status']);
            }

            if (!$canUpdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this investment'
                ], 403);
            }

            // Recalculate equity if amount changed
            if (isset($updateData['amount']) && $updateData['amount'] !== $investment->amount) {
                $newEquityPercentage = ($updateData['amount'] / $investment->business->valuation) * 100;
                $updateData['equity_percentage'] = round($newEquityPercentage, 4);
            }

            $investment->update($updateData);

            Log::info('Investment updated successfully', [
                'investment_id' => $investment->id,
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investment updated successfully',
                'data' => [
                    'investment' => $investment->load(['business', 'investor'])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Investment update failed', [
                'investment_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Investment update failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified investment.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $investment = Investment::find($id);

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            // Only investor can delete their own pending investment or admin
            if ($investment->investor_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this investment'
                ], 403);
            }

            // Can only delete pending investments
            if ($investment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete non-pending investments'
                ], 422);
            }

            $investment->delete();

            Log::info('Investment deleted successfully', [
                'investment_id' => $investment->id,
                'investor_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investment deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Investment deletion failed', [
                'investment_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Investment deletion failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Approve an investment (business owner only).
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $investment = Investment::with(['business'])->find($id);

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            // Check if user owns the business
            if ($investment->business->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only business owners can approve investments'
                ], 403);
            }

            if ($investment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment is not pending approval'
                ], 422);
            }

            $investment->update(['status' => 'approved']);

            Log::info('Investment approved', [
                'investment_id' => $investment->id,
                'business_owner_id' => $user->id,
                'investor_id' => $investment->investor_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investment approved successfully',
                'data' => [
                    'investment' => $investment->load(['business', 'investor'])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Investment approval failed', [
                'investment_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Investment approval failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Reject an investment (business owner only).
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:1000'
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
            $investment = Investment::with(['business'])->find($id);

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            // Check if user owns the business
            if ($investment->business->seller_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only business owners can reject investments'
                ], 403);
            }

            if ($investment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment is not pending approval'
                ], 422);
            }

            $updateData = ['status' => 'rejected'];
            if ($request->reason) {
                // Store rejection reason in message field
                $updateData['message'] = 'Rejection reason: ' . $request->reason;
            }

            $investment->update($updateData);

            Log::info('Investment rejected', [
                'investment_id' => $investment->id,
                'business_owner_id' => $user->id,
                'investor_id' => $investment->investor_id,
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investment rejected successfully',
                'data' => [
                    'investment' => $investment->load(['business', 'investor'])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Investment rejection failed', [
                'investment_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Investment rejection failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get investments for a specific business.
     */
    public function byBusiness(Request $request, string $businessId): JsonResponse
    {
        try {
            $user = $request->user();
            $business = Business::find($businessId);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            // Check permissions - business owner, admin, or public if business is active
            $canView = ($business->seller_id === $user->id) || 
                      ($user->isAdmin()) ||
                      ($business->status === 'active');

            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view investments for this business'
                ], 403);
            }

            $query = Investment::with(['investor'])
                ->where('business_id', $businessId);

            // Only show completed investments to public
            if ($business->seller_id !== $user->id && !$user->isAdmin()) {
                $query->where('status', 'completed');
            }

            $investments = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $investments
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch business investments', [
                'business_id' => $businessId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business investments'
            ], 500);
        }
    }

    /**
     * Get user's own investments (as investor).
     */
    public function getMyInvestments(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $investments = Investment::with(['business', 'business.seller'])
                ->where('investor_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $investments
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user investments', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch investments'
            ], 500);
        }
    }

    /**
     * Get user's investment portfolio.
     */
    public function getUserPortfolio(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $investments = Investment::with(['business'])
                ->where('investor_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate portfolio statistics
            $totalInvested = $investments->sum('amount');
            $activeInvestments = $investments->where('status', 'completed')->count();
            $pendingInvestments = $investments->where('status', 'pending')->count();
            $approvedInvestments = $investments->where('status', 'approved')->count();

            // Mock portfolio value calculation (in real app, this would be based on business performance)
            $portfolioValue = $totalInvested * 1.12; // Assuming 12% average return

            return response()->json([
                'success' => true,
                'data' => [
                    'investments' => $investments,
                    'stats' => [
                        'total_invested' => $totalInvested,
                        'total_investments' => $investments->count(),
                        'active_investments' => $activeInvestments,
                        'pending_investments' => $pendingInvestments,
                        'approved_investments' => $approvedInvestments,
                        'portfolio_value' => $portfolioValue
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user portfolio', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch investment portfolio'
            ], 500);
        }
    }

    /**
     * Calculate potential returns for an investment.
     */
    public function calculateReturns(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|uuid|exists:businesses,id',
            'amount' => 'required|numeric|min:100|max:999999999.99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $business = Business::find($request->business_id);
            
            if (!$business || $business->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not available for investment'
                ], 404);
            }

            $amount = $request->amount;
            $equityPercentage = ($amount / $business->valuation) * 100;
            
            // Mock potential return calculation (in real app, this would be more sophisticated)
            $potentialReturn = $amount * 0.15; // Assuming 15% potential return
            $estimatedValue = $amount + $potentialReturn;

            return response()->json([
                'success' => true,
                'data' => [
                    'equity_percentage' => round($equityPercentage, 4),
                    'potential_return' => round($potentialReturn, 2),
                    'estimated_value' => round($estimatedValue, 2),
                    'business_valuation' => $business->valuation,
                    'investment_amount' => $amount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to calculate investment returns', [
                'business_id' => $request->business_id,
                'amount' => $request->amount,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate investment returns'
            ], 500);
        }
    }
}
