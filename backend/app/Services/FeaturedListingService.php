<?php

namespace App\Services;

use App\Models\FeaturedListing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FeaturedListingService
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function approve(FeaturedListing $listing, User $admin): FeaturedListing
    {
        if ($listing->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending listings can be approved');
        }

        $listing->approve($admin->id);

        return $listing->fresh(['listable', 'user', 'transaction']);
    }

    public function reject(FeaturedListing $listing, User $admin, string $reason): FeaturedListing
    {
        if ($listing->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending listings can be rejected');
        }

        $listing->reject($admin->id, $reason);

        if ($listing->transaction_id) {
            $transaction = Transaction::find($listing->transaction_id);
            if ($transaction && $transaction->status === 'completed') {
                $refund = $this->paymentService->processRefund($transaction);
                if (! ($refund['success'] ?? false)) {
                    Log::warning('Featured listing refund failed', [
                        'listing_id' => $listing->id,
                        'transaction_id' => $transaction->id,
                        'error' => $refund['error'] ?? 'unknown',
                    ]);
                }
            }
        }

        return $listing->fresh(['listable', 'user', 'transaction']);
    }
}
