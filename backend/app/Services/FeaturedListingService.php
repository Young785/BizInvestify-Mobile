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

        $listing = $listing->fresh(['listable', 'user', 'transaction']);
        $this->notifyListingReview($listing, 'listing_approved', 'Featured Listing Approved', 'is now live.');

        return $listing;
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

        $listing = $listing->fresh(['listable', 'user', 'transaction']);
        $this->notifyListingReview(
            $listing,
            'listing_rejected',
            'Featured Listing Rejected',
            'was not approved.'.($reason ? ' Reason: '.$reason : '')
        );

        return $listing;
    }

    private function notifyListingReview(
        FeaturedListing $listing,
        string $type,
        string $title,
        string $messageSuffix
    ): void {
        $owner = $listing->user;
        if (! $owner) {
            return;
        }

        app(NotificationService::class)->notifyWithEmail(
            $owner,
            $type,
            $title,
            "Your promotion \"{$listing->title}\" {$messageSuffix}",
            [
                'featured_listing_id' => $listing->id,
                'action_url' => '/dashboard/featured-listings',
                'action_label' => 'View Promotions',
                'email_details' => [
                    'Title' => $listing->title,
                    'Type' => ucfirst((string) ($listing->promotion_type ?? 'featured')),
                    'Status' => ucfirst((string) $listing->status),
                ],
            ],
            'high',
            "{$title}: {$listing->title}"
        );
    }
}
