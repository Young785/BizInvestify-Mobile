<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalService
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function approve(Withdrawal $withdrawal, User $admin, ?string $notes = null): Withdrawal
    {
        if ($withdrawal->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending withdrawals can be approved');
        }

        $withdrawal->update([
            'status' => 'processing',
            'notes' => $notes ?? $withdrawal->notes,
        ]);

        Log::info('Withdrawal approved', [
            'withdrawal_id' => $withdrawal->id,
            'admin_id' => $admin->id,
        ]);

        return $withdrawal->fresh(['bankAccount', 'user']);
    }

    public function reject(Withdrawal $withdrawal, User $admin, string $reason): Withdrawal
    {
        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            throw new \InvalidArgumentException('Withdrawal cannot be rejected in its current state');
        }

        $withdrawal->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'processed_at' => now(),
        ]);

        Log::info('Withdrawal rejected', [
            'withdrawal_id' => $withdrawal->id,
            'admin_id' => $admin->id,
            'reason' => $reason,
        ]);

        return $withdrawal->fresh(['bankAccount', 'user']);
    }

    public function complete(Withdrawal $withdrawal, User $admin, ?string $payoutReference = null): Withdrawal
    {
        if ($withdrawal->status !== 'processing') {
            throw new \InvalidArgumentException('Only processing withdrawals can be completed');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $payoutReference) {
            $user = $withdrawal->user;

            $balance = $this->paymentService->getWalletBalance($user);
            $reserved = Withdrawal::where('user_id', $user->id)
                ->where('id', '!=', $withdrawal->id)
                ->whereIn('status', ['pending', 'processing'])
                ->sum('amount');

            if ($withdrawal->amount > ($balance - $reserved)) {
                throw new \InvalidArgumentException('User has insufficient balance to complete this withdrawal');
            }

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $withdrawal->amount,
                'currency' => $withdrawal->currency,
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => 'bank_transfer',
                'reference_id' => $payoutReference ?? $withdrawal->reference,
                'notes' => 'Withdrawal to bank account #'.$withdrawal->bank_account_id,
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'bank_account_id' => $withdrawal->bank_account_id,
                    'processed_by' => $admin->id,
                ],
                'completed_at' => now(),
            ]);

            $withdrawal->update([
                'status' => 'completed',
                'processed_at' => now(),
                'notes' => trim(($withdrawal->notes ? $withdrawal->notes.' ' : '').($payoutReference ? "Ref: {$payoutReference}" : '')),
            ]);

            Log::info('Withdrawal completed', [
                'withdrawal_id' => $withdrawal->id,
                'admin_id' => $admin->id,
                'amount' => $withdrawal->amount,
            ]);

            return $withdrawal->fresh(['bankAccount', 'user']);
        });
    }
}
