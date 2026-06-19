<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    public const RELEASE_CONDITIONS = [
        'due_diligence_completed' => 'Due diligence completed',
        'legal_documents_signed' => 'Legal documents signed',
        'transfer_completed' => 'Ownership transfer completed',
    ];

    public const BUYER_CONDITIONS = ['due_diligence_completed', 'transfer_completed'];

    public const SELLER_CONDITIONS = ['legal_documents_signed'];

    public function __construct(private PaymentService $payments)
    {
    }

    public function listForUser(User $user, ?string $status = null, ?string $role = null)
    {
        $query = Transaction::query()
            ->where('type', 'escrow')
            ->with(['buyer:id,first_name,last_name,email', 'seller:id,first_name,last_name,email']);

        if ($role === 'buyer') {
            $query->where('buyer_id', $user->id);
        } elseif ($role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($status && $status !== 'all') {
            if ($status === 'funded') {
                $query->where('status', 'processing');
            } elseif ($status === 'released') {
                $query->where('status', 'completed')
                    ->whereNotNull('metadata->released_at');
            } else {
                $query->where('status', $status);
            }
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    public function findForUser(string $escrowId, User $user): ?Transaction
    {
        return Transaction::where('reference_id', $escrowId)
            ->where('type', 'escrow')
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->first();
    }

    public function format(Transaction $transaction): array
    {
        $metadata = $this->metadataArray($transaction);
        $businessId = $metadata['business_id'] ?? $transaction->listing_id;
        $business = $businessId ? Business::find($businessId) : null;
        $conditions = $metadata['release_conditions'] ?? $this->defaultConditions();
        $released = ! empty($metadata['released_at']);

        return [
            'id' => $transaction->id,
            'escrow_id' => $transaction->reference_id,
            'amount' => (float) $transaction->amount,
            'currency' => strtoupper($transaction->currency ?? 'USD'),
            'status' => $released ? 'released' : ($transaction->status === 'processing' ? 'funded' : $transaction->status),
            'transaction_status' => $transaction->status,
            'business_id' => $businessId,
            'business_name' => $business?->name,
            'business_slug' => $business?->slug,
            'buyer_id' => $transaction->buyer_id,
            'seller_id' => $transaction->seller_id,
            'buyer' => $transaction->buyer ? [
                'id' => $transaction->buyer->id,
                'name' => trim($transaction->buyer->first_name.' '.$transaction->buyer->last_name),
                'email' => $transaction->buyer->email,
            ] : null,
            'seller' => $transaction->seller ? [
                'id' => $transaction->seller->id,
                'name' => trim($transaction->seller->first_name.' '.$transaction->seller->last_name),
                'email' => $transaction->seller->email,
            ] : null,
            'release_conditions' => $conditions,
            'condition_labels' => self::RELEASE_CONDITIONS,
            'funded_at' => $metadata['funded_at'] ?? null,
            'released_at' => $metadata['released_at'] ?? null,
            'created_at' => $transaction->created_at,
            'can_release' => $this->allConditionsMet($conditions) && $transaction->status === 'processing' && ! $released,
        ];
    }

    public function updateConditions(string $escrowId, User $user, array $conditions): Transaction
    {
        $transaction = $this->findForUser($escrowId, $user);

        if (! $transaction || $transaction->status !== 'processing') {
            throw new \InvalidArgumentException('Escrow is not open for milestone updates');
        }

        $metadata = $this->metadataArray($transaction);
        if (! empty($metadata['released_at'])) {
            throw new \InvalidArgumentException('Escrow has already been released');
        }

        $current = $metadata['release_conditions'] ?? $this->defaultConditions();
        $isBuyer = (int) $transaction->buyer_id === (int) $user->id;
        $isSeller = (int) $transaction->seller_id === (int) $user->id;

        foreach ($conditions as $key => $value) {
            if (! array_key_exists($key, self::RELEASE_CONDITIONS)) {
                continue;
            }
            if ($isBuyer && in_array($key, self::BUYER_CONDITIONS, true)) {
                $current[$key] = (bool) $value;
            }
            if ($isSeller && in_array($key, self::SELLER_CONDITIONS, true)) {
                $current[$key] = (bool) $value;
            }
        }

        $transaction->update([
            'metadata' => array_merge($metadata, [
                'release_conditions' => $current,
                'conditions_updated_at' => now()->toISOString(),
                'conditions_updated_by' => $user->id,
            ]),
        ]);

        return $transaction->fresh(['buyer', 'seller']);
    }

    public function release(string $escrowId, User $user, array $conditions): array
    {
        if ((int) $user->id === 0) {
            throw new \InvalidArgumentException('Unauthorized');
        }

        $transaction = Transaction::where('reference_id', $escrowId)
            ->where('type', 'escrow')
            ->where('status', 'processing')
            ->first();

        if (! $transaction) {
            throw new \InvalidArgumentException('Funded escrow transaction not found');
        }

        if ((int) $transaction->buyer_id !== (int) $user->id) {
            throw new \InvalidArgumentException('Only the buyer can release escrow funds');
        }

        $metadata = $this->metadataArray($transaction);
        if (! empty($metadata['released_at'])) {
            throw new \InvalidArgumentException('Escrow already released');
        }

        $merged = array_merge($metadata['release_conditions'] ?? $this->defaultConditions(), $conditions);

        if (! $this->allConditionsMet($merged)) {
            throw new \InvalidArgumentException('All release conditions must be completed before funds are released');
        }

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now(),
            'metadata' => array_merge($metadata, [
                'release_conditions' => $merged,
                'released_at' => now()->toISOString(),
                'released_by' => $user->id,
                'escrow_status' => 'released',
            ]),
        ]);

        $sellerId = $metadata['seller_id'] ?? $transaction->seller_id;
        if ($sellerId) {
            $this->payments->creditSellerPayout(
                $transaction,
                (int) $sellerId,
                (float) $transaction->net_amount ?: (float) $transaction->amount,
                'escrow_release'
            );
        }

        return [
            'success' => true,
            'escrow_id' => $escrowId,
            'amount' => (float) $transaction->amount,
            'released_at' => now()->toISOString(),
        ];
    }

    public function markFunded(Transaction $transaction): void
    {
        $metadata = $this->metadataArray($transaction);
        $transaction->update([
            'status' => 'processing',
            'metadata' => array_merge($metadata, [
                'funded_at' => now()->toISOString(),
                'escrow_status' => 'funded',
                'release_conditions' => $metadata['release_conditions'] ?? $this->defaultConditions(),
            ]),
        ]);
    }

    public function defaultConditions(): array
    {
        return array_fill_keys(array_keys(self::RELEASE_CONDITIONS), false);
    }

    public function allConditionsMet(array $conditions): bool
    {
        foreach (array_keys(self::RELEASE_CONDITIONS) as $key) {
            if (empty($conditions[$key])) {
                return false;
            }
        }

        return true;
    }

    private function metadataArray(Transaction $transaction): array
    {
        $metadata = $transaction->metadata ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?? [];
        }

        return is_array($metadata) ? $metadata : [];
    }
}
