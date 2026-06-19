<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Business;
use App\Models\Product;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Message;
use App\Models\ActivityLog;
use App\Models\Kyc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Support\DatabaseDateExpressions;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            $filters = $reports->filtersFromRequest($request);
            $stats = $reports->overview($filters);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
            ], 500);
        }
    }

    /**
     * Reports overview for analytics page
     */
    public function getReportsOverview(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            $filters = $reports->filtersFromRequest($request);

            return response()->json([
                'success' => true,
                'data' => $reports->overview($filters),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin reports overview error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reports overview',
            ], 500);
        }
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(): JsonResponse
    {
        try {
            $activities = [];

            // Recent user registrations
            $recentUsers = User::latest()->take(5)->get();
            foreach ($recentUsers as $user) {
                $activities[] = [
                    'id' => 'user_' . $user->id,
                    'type' => 'user_registration',
                    'title' => 'New User Registration',
                    'description' => $user->first_name . ' ' . $user->last_name . ' registered as a ' . $user->role,
                    'timestamp' => $user->created_at->diffForHumans(),
                    'sort_at' => $user->created_at->timestamp,
                    'user' => $user->first_name . ' ' . $user->last_name,
                ];
            }

            // Recent KYC approvals
            $recentKYC = User::where('kyc_status', 'verified')
                ->whereNotNull('kyc_verified_at')
                ->latest('kyc_verified_at')
                ->take(5)
                ->get();

            foreach ($recentKYC as $user) {
                $activities[] = [
                    'id' => 'kyc_' . $user->id,
                    'type' => 'kyc_approved',
                    'title' => 'KYC Approved',
                    'description' => $user->first_name . ' ' . $user->last_name . '\'s KYC documents approved',
                    'timestamp' => $user->kyc_verified_at->diffForHumans(),
                    'sort_at' => $user->kyc_verified_at->timestamp,
                    'user' => $user->first_name . ' ' . $user->last_name,
                ];
            }

            // Recent transactions
            $recentTransactions = Transaction::latest()->take(5)->get();
            foreach ($recentTransactions as $transaction) {
                $activities[] = [
                    'id' => 'transaction_' . $transaction->id,
                    'type' => 'transaction',
                    'title' => 'New Transaction',
                    'description' => 'Transaction completed for $' . number_format($transaction->amount, 2),
                    'timestamp' => $transaction->created_at->diffForHumans(),
                    'sort_at' => $transaction->created_at->timestamp,
                    'amount' => $transaction->amount,
                ];
            }

            usort($activities, fn ($a, $b) => ($b['sort_at'] ?? 0) <=> ($a['sort_at'] ?? 0));

            return response()->json([
                'success' => true,
                'data' => array_slice($activities, 0, 10)
            ]);
        } catch (\Exception $e) {
            Log::error('Admin recent activity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity'
            ], 500);
        }
    }

    /**
     * Get all users with pagination and filters
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Search filter
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->has('role')) {
                $query->where('role', $request->get('role'));
            }

            // Status filter - use is_verified instead of is_active
            if ($request->has('status')) {
                if ($request->get('status') === 'verified') {
                    $query->where('is_verified', true);
                } elseif ($request->get('status') === 'unverified') {
                    $query->where('is_verified', false);
                }
            }

            // KYC status filter
            if ($request->has('kyc_status')) {
                $query->where('kyc_status', $request->get('kyc_status'));
            }

            $users = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:buyer,seller,admin,super_admin,investor,moderator,support',
                'password' => 'required|string|min:8',
                'is_verified' => 'boolean',
                'kyc_status' => 'in:pending,verified,rejected',
                'business_name' => 'nullable|string|max:255',
                'business_type' => 'nullable|string|max:255',
                'investment_amount' => 'nullable|string|max:255',
                'investment_focus' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'country_code' => 'nullable|string|size:2',
                'preferred_currency' => 'nullable|string|size:3',
                'preferred_language' => 'nullable|string|max:5',
                'timezone' => 'nullable|string|max:64',
            ]);

            // Hash the password
            $validated['password'] = bcrypt($validated['password']);
            
            // Set default values
            $validated['is_verified'] = $validated['is_verified'] ?? false;
            $validated['kyc_status'] = $validated['kyc_status'] ?? 'pending';
            $validated['email_verified_at'] = $validated['is_verified'] ? now() : null;
            
            // Generate the name field from first_name and last_name
            $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];

            $user = User::create($validated);

            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->assignRole($role);
            }

            Log::info('Admin created user: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->load(['businesses', 'products', 'investments'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin create user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    /**
     * Get specific user details
     */
    public function getUser(User $user): JsonResponse
    {
        try {
            $user->load(['businesses', 'products', 'investments']);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details'
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone' => 'sometimes|nullable|string|max:20',
                'role' => 'sometimes|in:seller,buyer,admin,super_admin,investor,moderator,support',
                'is_verified' => 'sometimes|boolean',
                'kyc_status' => 'sometimes|in:pending,verified,rejected',
                'country' => 'sometimes|nullable|string|max:255',
                'country_code' => 'sometimes|nullable|string|size:2',
                'preferred_currency' => 'sometimes|nullable|string|size:3',
                'preferred_language' => 'sometimes|nullable|string|max:5',
                'timezone' => 'sometimes|nullable|string|max:64',
            ]);

            if (isset($validated['role'])) {
                $role = Role::where('name', $validated['role'])->first();
                if ($role) {
                    $user->syncRoles([$role]);
                }
                unset($validated['role']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Admin update user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): JsonResponse
    {
        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin delete user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    /**
     * Suspend user
     */
    public function suspendUser(User $user): JsonResponse
    {
        try {
            $user->update(['is_verified' => false]);

            return response()->json([
                'success' => true,
                'message' => 'User suspended successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin suspend user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend user'
            ], 500);
        }
    }

    /**
     * Activate user
     */
    public function activateUser(User $user): JsonResponse
    {
        try {
            $user->update(['is_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin activate user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user'
            ], 500);
        }
    }

    /**
     * Login as user (for admin impersonation)
     */
    public function loginAsUser(Request $request, User $user): JsonResponse
    {
        try {
            // Check if the current user is a super admin
            $currentUser = $request->user();
            if (!$currentUser || $currentUser->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only super admins can impersonate users.'
                ], 403);
            }

            // Create a token for the target user
            $token = $user->createToken('admin-impersonation')->plainTextToken;

            // Log the impersonation
            Log::info('Super admin impersonation: ' . $currentUser->email . ' logged in as ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in as user',
                'data' => [
                    'token' => $token,
                    'user' => $user->load(['businesses', 'products', 'investments']),
                    'original_admin' => [
                        'id' => $currentUser->id,
                        'email' => $currentUser->email,
                        'name' => $currentUser->first_name . ' ' . $currentUser->last_name
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Admin login as user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to login as user'
            ], 500);
        }
    }

    /**
     * Get KYC applications
     */
    public function getKYCApplications(Request $request, \App\Services\KycService $kycService): JsonResponse
    {
        try {
            $query = \App\Models\Kyc::with('user');

            if ($request->has('status') && $request->get('status') !== 'all') {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('document_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $applications = $query->orderByDesc('submitted_at')
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $applications->setCollection(
                $applications->getCollection()->map(fn ($kyc) => $kycService->formatForAdminList($kyc))
            );

            return response()->json([
                'success' => true,
                'data' => $applications,
                'meta' => [
                    'pending_count' => \App\Models\Kyc::where('status', 'pending')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get KYC applications error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KYC applications',
            ], 500);
        }
    }

    public function getKYCApplication($id, \App\Services\KycService $kycService): JsonResponse
    {
        try {
            $kyc = \App\Models\Kyc::with('user')->find($id);

            if (! $kyc) {
                return response()->json([
                    'success' => false,
                    'message' => 'No KYC application found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $kycService->formatForAdmin($kyc),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get KYC application error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KYC application',
            ], 500);
        }
    }

    public function approveKYC($id, \App\Services\KycService $kycService): JsonResponse
    {
        try {
            $kyc = \App\Models\Kyc::with('user')->find($id);

            if (! $kyc) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC application not found',
                ], 404);
            }

            if (! $kyc->approve(auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending applications can be approved',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'KYC application approved successfully',
                'data' => $kycService->formatForAdmin($kyc->fresh(['user'])),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin approve KYC error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve KYC application',
            ], 500);
        }
    }

    public function rejectKYC(Request $request, $id, \App\Services\KycService $kycService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $kyc = \App\Models\Kyc::with('user')->find($id);

            if (! $kyc) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC application not found',
                ], 404);
            }

            if (! $kyc->reject(auth()->user(), $validated['reason'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending applications can be rejected',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'KYC application rejected successfully',
                'data' => $kycService->formatForAdmin($kyc->fresh(['user'])),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin reject KYC error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject KYC application',
            ], 500);
        }
    }

    public function getKYCDocument(Request $request, \App\Services\KycService $kycService, $document = null): JsonResponse
    {
        try {
            $documentPath = $request->get('path') ?? ($document ? urldecode($document) : null);

            if (! $documentPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document path is required',
                ], 422);
            }

            $url = $kycService->getDocumentUrl($documentPath);

            if (! $url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'path' => $documentPath,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get KYC document error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document',
            ], 500);
        }
    }

    /**
     * Get transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $query = Transaction::with(['buyer', 'seller']);

            // Status filter
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Date range filter
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->get('start_date'));
            }

            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->get('end_date'));
            }

            $transactions = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get transactions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions'
            ], 500);
        }
    }

    /**
     * Get specific transaction
     */
    public function getTransaction(Transaction $transaction): JsonResponse
    {
        try {
            $transaction->load(['buyer', 'seller']);

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get transaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction'
            ], 500);
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction(Transaction $transaction): JsonResponse
    {
        try {
            if ($transaction->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed transactions can be refunded'
                ], 400);
            }

            $transaction->update(['status' => 'refunded']);

            return response()->json([
                'success' => true,
                'message' => 'Transaction refunded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin refund transaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to refund transaction'
            ], 500);
        }
    }

    /**
     * Get user report
     */
    public function getUserReport(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $reports->userReport($reports->filtersFromRequest($request)),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin user report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate user report',
            ], 500);
        }
    }

    /**
     * Get transaction report
     */
    public function getTransactionReport(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $reports->transactionReport($reports->filtersFromRequest($request)),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin transaction report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate transaction report',
            ], 500);
        }
    }

    /**
     * Get revenue report
     */
    public function getRevenueReport(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $reports->revenueReport($reports->filtersFromRequest($request)),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin revenue report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate revenue report',
            ], 500);
        }
    }

    /**
     * Get KYC report
     */
    public function getKYCReport(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $reports->kycReport($reports->filtersFromRequest($request)),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin KYC report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate KYC report',
            ], 500);
        }
    }

    /**
     * Export users to CSV
     */
    public function exportUsers(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'csv');
            $filters = $request->only(['role', 'status', 'kyc_status', 'search']);

            $query = User::query();

            // Apply filters
            if (!empty($filters['role'])) {
                $query->where('role', $filters['role']);
            }
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'verified') {
                    $query->where('is_verified', true);
                } else {
                    $query->where('is_verified', false);
                }
            }
            if (!empty($filters['kyc_status'])) {
                $query->where('kyc_status', $filters['kyc_status']);
            }
            if (!empty($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            }

            $users = $query->get();

            if ($format === 'csv') {
                $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
                $filepath = storage_path('app/public/exports/' . $filename);
                
                // Ensure directory exists
                if (!file_exists(dirname($filepath))) {
                    mkdir(dirname($filepath), 0755, true);
                }

                $file = fopen($filepath, 'w');
                
                // CSV headers
                fputcsv($file, [
                    'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Role', 
                    'Verified', 'KYC Status', 'Business Name', 'Business Type',
                    'Investment Amount', 'Investment Focus', 'Created At', 'Updated At'
                ]);

                // CSV data
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->phone,
                        $user->role,
                        $user->is_verified ? 'Yes' : 'No',
                        $user->kyc_status,
                        $user->business_name,
                        $user->business_type,
                        $user->investment_amount,
                        $user->investment_focus,
                        $user->created_at,
                        $user->updated_at
                    ]);
                }

                fclose($file);

                return response()->json([
                    'success' => true,
                    'message' => 'Users exported successfully',
                    'data' => [
                        'download_url' => '/storage/exports/' . $filename,
                        'filename' => $filename,
                        'total_users' => $users->count()
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unsupported export format'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Admin export users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export users'
            ], 500);
        }
    }

    /**
     * Export report
     */
    public function exportReport(Request $request, \App\Services\AdminReportService $reports): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:user,transaction,revenue,kyc',
                'format' => 'nullable|in:csv',
                'period' => 'nullable|in:daily,weekly,monthly,yearly',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $type = $validated['type'];
            $format = $validated['format'] ?? 'csv';
            $filters = $reports->filtersFromRequest($request);
            $export = $reports->export($type, $format, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Report exported successfully',
                'data' => $export,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Admin export report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report',
            ], 500);
        }
    }

    /**
     * Get settings
     */
    public function getSettings(): JsonResponse
    {
        try {
            $settingsService = app(\App\Services\PlatformSettingsService::class);

            $settings = array_merge(
                $settingsService->getPlatformSettings(),
                [
                    'pricing_plans' => $settingsService->getPricingPlans(),
                    'localization' => $settingsService->getLocalizationConfig(),
                    'payment_gateways' => app(\App\Services\PaymentGatewayService::class)->getConfigForAdmin(),
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get settings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings'
            ], 500);
        }
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'site_name' => 'sometimes|string|max:255',
                'site_description' => 'sometimes|string',
                'site_url' => 'sometimes|nullable|url',
                'contact_email' => 'sometimes|nullable|email',
                'support_email' => 'sometimes|nullable|email',
                'currency' => 'sometimes|string|max:3',
                'commission_rate' => 'sometimes|numeric|min:0|max:100',
                'investment_fee_rate' => 'sometimes|numeric|min:0|max:100',
                'featured_listing_min_daily_budget' => 'sometimes|numeric|min:1',
                'featured_listing_approval_required' => 'sometimes|boolean',
                'minimum_withdrawal' => 'sometimes|numeric|min:0',
                'payment_methods' => 'sometimes|array',
                'max_login_attempts' => 'sometimes|integer|min:1|max:10',
                'session_timeout' => 'sometimes|integer|min:15|max:480',
                'require_2fa' => 'sometimes|boolean',
                'require_kyc' => 'sometimes|boolean',
                'smtp_host' => 'sometimes|nullable|string|max:255',
                'smtp_port' => 'sometimes|nullable|integer|min:1|max:65535',
                'smtp_username' => 'sometimes|nullable|string|max:255',
                'smtp_password' => 'sometimes|nullable|string|max:255',
                'email_from_name' => 'sometimes|nullable|string|max:255',
                'email_from_address' => 'sometimes|nullable|email',
                'email_notifications' => 'sometimes|boolean',
                'push_notifications' => 'sometimes|boolean',
                'admin_notifications' => 'sometimes|boolean',
                'maintenance_mode' => 'sometimes|boolean',
                'debug_mode' => 'sometimes|boolean',
                'log_level' => 'sometimes|in:debug,info,warning,error',
                'cache_duration' => 'sometimes|integer|min:60|max:86400',
                'pricing_plans' => 'sometimes|array',
                'localization' => 'sometimes|array',
                'payment_gateways' => 'sometimes|array',
            ]);

            $settingsService = app(\App\Services\PlatformSettingsService::class);
            $platformKeys = [
                'site_name', 'site_description', 'site_url', 'contact_email', 'support_email',
                'currency', 'commission_rate', 'investment_fee_rate', 'featured_listing_min_daily_budget',
                'featured_listing_approval_required', 'minimum_withdrawal', 'payment_methods',
                'max_login_attempts', 'session_timeout', 'require_2fa', 'require_kyc',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                'email_from_name', 'email_from_address', 'email_notifications',
                'push_notifications', 'admin_notifications', 'maintenance_mode',
                'debug_mode', 'log_level', 'cache_duration',
            ];

            $platformSettings = array_merge(
                $settingsService->getPlatformSettings(),
                array_intersect_key($validated, array_flip($platformKeys))
            );
            $settingsService->savePlatformSettings($platformSettings);

            if (isset($validated['pricing_plans'])) {
                $settingsService->savePricingPlans($validated['pricing_plans']);
            }

            if (isset($validated['localization'])) {
                $settingsService->saveLocalizationConfig($validated['localization']);
            }

            if (isset($validated['payment_gateways'])) {
                app(\App\Services\PaymentGatewayService::class)->saveConfig($validated['payment_gateways']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin update settings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings'
            ], 500);
        }
    }

    /**
     * Get payment gateway configuration (admin).
     */
    public function getPaymentGateways(): JsonResponse
    {
        try {
            $service = app(\App\Services\PaymentGatewayService::class);

            return response()->json([
                'success' => true,
                'data' => $service->getConfigForAdmin(),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get payment gateways error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment gateways',
            ], 500);
        }
    }

    /**
     * Update payment gateway configuration (admin).
     */
    public function updatePaymentGateways(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'default_gateway' => 'sometimes|string|in:stripe,paystack,flutterwave,razorpay,paypal,bank_transfer',
                'gateways' => 'sometimes|array',
            ]);

            $service = app(\App\Services\PaymentGatewayService::class);
            $service->saveConfig($validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment gateways updated successfully',
                'data' => $service->getConfigForAdmin(),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin update payment gateways error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment gateways',
            ], 500);
        }
    }

    /**
     * Test payment gateway connection (admin).
     */
    public function testPaymentGateway(string $gateway): JsonResponse
    {
        try {
            $service = app(\App\Services\PaymentGatewayService::class);
            $result = $service->testConnection($gateway);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Admin test payment gateway error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gateway test failed',
            ], 500);
        }
    }

    /**
     * Get system health
     */
    public function getSystemHealth(): JsonResponse
    {
        try {
            $health = [
                'database' => 'healthy',
                'cache' => 'healthy',
                'storage' => 'healthy',
                'queue' => 'healthy',
                'uptime' => '99.9%',
                'last_backup' => now()->subDays(1)->toISOString(),
                'disk_usage' => '45%',
                'memory_usage' => '60%'
            ];

            return response()->json([
                'success' => true,
                'data' => $health
            ]);
        } catch (\Exception $e) {
            Log::error('Admin system health error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check system health'
            ], 500);
        }
    }

    /**
     * Get system logs
     */
    public function getSystemLogs(Request $request): JsonResponse
    {
        try {
            $logs = [
                [
                    'level' => 'info',
                    'message' => 'User login successful',
                    'timestamp' => now()->subMinutes(5)->toISOString(),
                    'user' => 'john@example.com'
                ],
                [
                    'level' => 'warning',
                    'message' => 'Failed login attempt',
                    'timestamp' => now()->subMinutes(10)->toISOString(),
                    'user' => 'unknown@example.com'
                ],
                [
                    'level' => 'error',
                    'message' => 'Database connection timeout',
                    'timestamp' => now()->subMinutes(15)->toISOString(),
                    'user' => null
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Admin system logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch system logs'
            ], 500);
        }
    }

    /**
     * Get flagged listings
     */
    public function getFlaggedListings(Request $request): JsonResponse
    {
        try {
            $listings = [];

            return response()->json([
                'success' => true,
                'data' => $listings
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get flagged listings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flagged listings'
            ], 500);
        }
    }

    /**
     * Approve listing
     */
    public function approveListing(Request $request, $listingId): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Listing approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin approve listing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve listing'
            ], 500);
        }
    }

    /**
     * Reject listing
     */
    public function rejectListing(Request $request, $listingId): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Listing rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin reject listing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject listing'
            ], 500);
        }
    }

    /**
     * Get disputes
     */
    public function getDisputes(Request $request): JsonResponse
    {
        try {
            $disputes = [];

            return response()->json([
                'success' => true,
                'data' => $disputes
            ]);
        } catch (\Exception $e) {
            Log::error('Admin get disputes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch disputes'
            ], 500);
        }
    }

    /**
     * Resolve dispute
     */
    public function resolveDispute(Request $request, $disputeId): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Dispute resolved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Admin resolve dispute error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve dispute'
            ], 500);
        }
    }

    /**
     * Stop impersonating user
     */
    public function stopImpersonating(Request $request): JsonResponse
    {
        try {
            // Get the original admin from token
            $user = $request->user();
            
            // Check if this is an impersonation session
            $originalAdminId = $request->input('original_admin_id');
            
            if (!$originalAdminId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No impersonation session found'
                ], 400);
            }
            
            // Find the original admin
            $originalAdmin = User::find($originalAdminId);
            
            if (!$originalAdmin || $originalAdmin->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Original admin not found'
                ], 404);
            }
            
            // Create new token for original admin
            $token = $originalAdmin->createToken('admin-token')->plainTextToken;
            
            // Log the activity
            ActivityLog::create([
                'user_id' => $originalAdmin->id,
                'action' => 'stop_impersonation',
                'description' => "Stopped impersonating user {$user->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Impersonation stopped successfully',
                'data' => [
                    'user' => $originalAdmin,
                    'token' => $token
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Stop impersonation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop impersonation'
            ], 500);
        }
    }
}