<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeConnectService
{
    public function __construct(
        private PaymentGatewayService $gateways,
    ) {
    }

    private function emptyRequirements(): array
    {
        return [
            'currently_due' => [],
            'past_due' => [],
            'disabled_reason' => null,
        ];
    }

    public function getStatus(User $user): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->configureStripe()) {
            return [
                'connected' => false,
                'stripe_enabled' => false,
                'status' => 'unavailable',
                'account_id' => null,
                'charges_enabled' => false,
                'payouts_enabled' => false,
                'details_submitted' => false,
                'requirements' => $this->emptyRequirements(),
                'message' => 'Stripe is not configured on this platform.',
            ];
        }

        if (!$user->stripe_account_id) {
            return [
                'connected' => false,
                'stripe_enabled' => true,
                'status' => 'not_connected',
                'account_id' => null,
                'charges_enabled' => false,
                'payouts_enabled' => false,
                'details_submitted' => false,
                'requirements' => $this->emptyRequirements(),
                'message' => 'Connect your Stripe account to receive card payouts.',
            ];
        }

        try {
            $account = Account::retrieve($user->stripe_account_id);

            return $this->formatAccount($account);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect status check failed: '.$e->getMessage(), ['user_id' => $user->id]);

            return [
                'connected' => false,
                'stripe_enabled' => true,
                'status' => 'error',
                'account_id' => $user->stripe_account_id,
                'charges_enabled' => false,
                'payouts_enabled' => false,
                'details_submitted' => false,
                'requirements' => $this->emptyRequirements(),
                'message' => 'Unable to load Stripe account status. Please try again.',
            ];
        }
    }

    public function startOnboarding(User $user, ?string $returnUrl = null, ?string $refreshUrl = null): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->configureStripe()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        try {
            $accountId = $this->ensureConnectAccount($user);
            $urls = $this->resolveUrls($returnUrl, $refreshUrl);

            $link = AccountLink::create([
                'account' => $accountId,
                'refresh_url' => $urls['refresh_url'],
                'return_url' => $urls['return_url'],
                'type' => 'account_onboarding',
            ]);

            return [
                'success' => true,
                'account_id' => $accountId,
                'onboarding_url' => $link->url,
                'status' => $this->getStatus($user->fresh()),
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect onboarding failed: '.$e->getMessage(), ['user_id' => $user->id]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createDashboardLink(User $user): array
    {
        if (!$user->stripe_account_id) {
            return ['success' => false, 'error' => 'No Stripe account found'];
        }

        if (!$this->gateways->isEnabled('stripe') || !$this->configureStripe()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        try {
            $link = \Stripe\Account::createLoginLink($user->stripe_account_id);

            return [
                'success' => true,
                'dashboard_url' => $link->url,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect dashboard link failed: '.$e->getMessage(), ['user_id' => $user->id]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function syncAccountFromWebhook(object $account): void
    {
        $userId = $account->metadata->user_id ?? null;
        if (!$userId) {
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        if ($user->stripe_account_id && $user->stripe_account_id !== $account->id) {
            return;
        }

        $user->update(['stripe_account_id' => $account->id]);
    }

    private function ensureConnectAccount(User $user): string
    {
        if ($user->stripe_account_id) {
            return $user->stripe_account_id;
        }

        $country = strtoupper($user->country_code ?: 'US');
        if (strlen($country) !== 2) {
            $country = 'US';
        }

        $account = Account::create([
            'type' => 'express',
            'country' => $country,
            'email' => $user->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_profile' => [
                'name' => trim($user->first_name.' '.$user->last_name) ?: $user->email,
            ],
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ]);

        $user->update(['stripe_account_id' => $account->id]);

        return $account->id;
    }

    private function formatAccount(Account $account): array
    {
        $requirements = $account->requirements ?? null;
        $currentlyDue = $requirements->currently_due ?? [];
        $pastDue = $requirements->past_due ?? [];
        $disabledReason = $requirements->disabled_reason ?? null;

        $chargesEnabled = (bool) $account->charges_enabled;
        $payoutsEnabled = (bool) $account->payouts_enabled;
        $detailsSubmitted = (bool) $account->details_submitted;

        $status = 'pending';
        if ($chargesEnabled && $payoutsEnabled) {
            $status = 'active';
        } elseif (!empty($pastDue) || $disabledReason) {
            $status = 'restricted';
        } elseif ($detailsSubmitted) {
            $status = 'review';
        }

        return [
            'connected' => true,
            'stripe_enabled' => true,
            'status' => $status,
            'account_id' => $account->id,
            'charges_enabled' => $chargesEnabled,
            'payouts_enabled' => $payoutsEnabled,
            'details_submitted' => $detailsSubmitted,
            'requirements' => [
                'currently_due' => $currentlyDue,
                'past_due' => $pastDue,
                'disabled_reason' => $disabledReason,
            ],
            'message' => $this->statusMessage($status, $currentlyDue, $pastDue),
        ];
    }

    private function statusMessage(string $status, array $currentlyDue, array $pastDue): string
    {
        return match ($status) {
            'active' => 'Your Stripe account is active and ready to receive payouts.',
            'restricted' => 'Action required: complete outstanding Stripe requirements to restore payouts.',
            'review' => 'Stripe is reviewing your account details.',
            default => empty($currentlyDue)
                ? 'Complete Stripe onboarding to enable payouts.'
                : 'Finish onboarding: '.implode(', ', array_slice($currentlyDue, 0, 3)).(count($currentlyDue) > 3 ? '…' : ''),
        };
    }

    private function resolveUrls(?string $returnUrl, ?string $refreshUrl): array
    {
        $base = rtrim(config('services.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');
        $defaultReturn = $base.'/dashboard/wallet?tab=payouts&stripe_connect=return';
        $defaultRefresh = $base.'/dashboard/wallet?tab=payouts&stripe_connect=refresh';

        return [
            'return_url' => $returnUrl ?: $defaultReturn,
            'refresh_url' => $refreshUrl ?: $defaultRefresh,
        ];
    }

    private function configureStripe(): bool
    {
        $secret = $this->gateways->getSecretKey('stripe');
        if (!$secret) {
            return false;
        }

        Stripe::setApiKey($secret);

        return true;
    }

    public function createTransfer(User $seller, float $amount, string $currency = 'usd', array $metadata = []): array
    {
        if ($amount <= 0 || ! $seller->stripe_account_id) {
            return ['success' => false, 'error' => 'Seller Stripe account not connected'];
        }

        if (! $this->gateways->isEnabled('stripe') || ! $this->configureStripe()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        $status = $this->getStatus($seller);
        if (($status['status'] ?? '') !== 'active' || ! ($status['payouts_enabled'] ?? false)) {
            return ['success' => false, 'error' => 'Seller Stripe account is not ready for payouts'];
        }

        try {
            $transfer = \Stripe\Transfer::create([
                'amount' => (int) round($amount * 100),
                'currency' => strtolower($currency),
                'destination' => $seller->stripe_account_id,
                'metadata' => collect($metadata)->map(fn ($value) => (string) $value)->all(),
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect transfer failed: '.$e->getMessage(), [
                'seller_id' => $seller->id,
                'amount' => $amount,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
