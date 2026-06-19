<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Withdrawal;
use App\Services\PaymentService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PlatformSettingsService $platformSettings
    ) {}

    public function summary(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $balance = $this->paymentService->getWalletBalance($user);
            $available = $this->paymentService->getAvailableWalletBalance($user);
            $settings = $this->platformSettings->getPlatformSettings();
            $minimumWithdrawal = (float) ($settings['minimum_withdrawal'] ?? 50);

            $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => round($balance, 2),
                    'available_balance' => round($available, 2),
                    'pending_withdrawals' => round((float) $pendingWithdrawals, 2),
                    'minimum_withdrawal' => $minimumWithdrawal,
                    'currency' => $user->preferred_currency ?? 'USD',
                    'bank_accounts_count' => BankAccount::where('user_id', $user->id)->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Wallet summary failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load wallet summary',
            ], 500);
        }
    }

    public function listBankAccounts(Request $request): JsonResponse
    {
        $accounts = BankAccount::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get()
            ->map(fn (BankAccount $account) => $account->toSafeArray());

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function storeBankAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|min:4|max:34',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:11',
            'iban' => 'nullable|string|max:34',
            'currency' => 'nullable|string|size:3',
            'country' => 'nullable|string|size:2',
            'account_type' => 'nullable|in:checking,savings,business',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $accountNumber = preg_replace('/\s+/', '', $request->account_number);
            $isDefault = $request->boolean('is_default') ||
                ! BankAccount::where('user_id', $user->id)->exists();

            return DB::transaction(function () use ($user, $request, $accountNumber, $isDefault) {
                if ($isDefault) {
                    BankAccount::where('user_id', $user->id)->update(['is_default' => false]);
                }

                $account = BankAccount::create([
                    'user_id' => $user->id,
                    'bank_name' => $request->bank_name,
                    'account_name' => $request->account_name,
                    'account_number' => $accountNumber,
                    'account_number_last4' => substr($accountNumber, -4),
                    'routing_number' => $request->routing_number,
                    'swift_code' => $request->swift_code,
                    'iban' => $request->iban,
                    'currency' => strtoupper($request->get('currency', $user->preferred_currency ?? 'USD')),
                    'country' => strtoupper($request->get('country', $user->country_code ?? 'US')),
                    'account_type' => $request->get('account_type', 'checking'),
                    'is_default' => $isDefault,
                    'status' => 'pending',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bank account added successfully',
                    'data' => $account->toSafeArray(),
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Failed to add bank account: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add bank account',
            ], 500);
        }
    }

    public function updateBankAccount(Request $request, int $id): JsonResponse
    {
        $account = BankAccount::where('user_id', $request->user()->id)->find($id);

        if (! $account) {
            return response()->json(['success' => false, 'message' => 'Bank account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => 'sometimes|string|max:255',
            'account_name' => 'sometimes|string|max:255',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:11',
            'iban' => 'nullable|string|max:34',
            'account_type' => 'nullable|in:checking,savings,business',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $account) {
                if ($request->boolean('is_default')) {
                    BankAccount::where('user_id', $account->user_id)->update(['is_default' => false]);
                    $account->is_default = true;
                }

                $account->fill($request->only([
                    'bank_name', 'account_name', 'routing_number',
                    'swift_code', 'iban', 'account_type',
                ]));
                $account->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Bank account updated',
                    'data' => $account->fresh()->toSafeArray(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to update bank account: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank account',
            ], 500);
        }
    }

    public function destroyBankAccount(Request $request, int $id): JsonResponse
    {
        $account = BankAccount::where('user_id', $request->user()->id)->find($id);

        if (! $account) {
            return response()->json(['success' => false, 'message' => 'Bank account not found'], 404);
        }

        $pending = Withdrawal::where('bank_account_id', $account->id)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove bank account with pending withdrawals',
            ], 422);
        }

        $wasDefault = $account->is_default;
        $userId = $account->user_id;
        $account->delete();

        if ($wasDefault) {
            $next = BankAccount::where('user_id', $userId)->oldest()->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank account removed',
        ]);
    }

    public function setDefaultBankAccount(Request $request, int $id): JsonResponse
    {
        $account = BankAccount::where('user_id', $request->user()->id)->find($id);

        if (! $account) {
            return response()->json(['success' => false, 'message' => 'Bank account not found'], 404);
        }

        DB::transaction(function () use ($account) {
            BankAccount::where('user_id', $account->user_id)->update(['is_default' => false]);
            $account->update(['is_default' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default bank account updated',
            'data' => $account->fresh()->toSafeArray(),
        ]);
    }

    public function listWithdrawals(Request $request): JsonResponse
    {
        $withdrawals = Withdrawal::with('bankAccount')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
                'last_page' => $withdrawals->lastPage(),
            ],
        ]);
    }

    public function requestWithdrawal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $account = BankAccount::where('user_id', $user->id)->find($request->bank_account_id);

            if (! $account) {
                return response()->json(['success' => false, 'message' => 'Bank account not found'], 404);
            }

            $settings = $this->platformSettings->getPlatformSettings();
            $minimum = (float) ($settings['minimum_withdrawal'] ?? 50);
            $amount = (float) $request->amount;

            if ($amount < $minimum) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum withdrawal amount is {$minimum}",
                ], 422);
            }

            $balance = $this->paymentService->getAvailableWalletBalance($user);

            if ($amount > $balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient available balance',
                ], 422);
            }

            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'amount' => $amount,
                'currency' => $account->currency,
                'status' => 'pending',
                'reference' => 'WD-'.strtoupper(Str::random(10)),
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted',
                'data' => $withdrawal->load('bankAccount'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Withdrawal request failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit withdrawal request',
            ], 500);
        }
    }

    public function cancelWithdrawal(Request $request, int $id): JsonResponse
    {
        $withdrawal = Withdrawal::where('user_id', $request->user()->id)->find($id);

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending withdrawals can be cancelled',
            ], 422);
        }

        $withdrawal->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal cancelled',
            'data' => $withdrawal,
        ]);
    }
}
