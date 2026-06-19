<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Investment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\PaymentGatewayService;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook as StripeWebhook;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    // NOTE: constructor defined once at top of class

    /**
     * Create product payment intent (Stripe)
     */
    public function createProductPaymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1',
            ]);

            $user = $request->user();
            $product = Product::findOrFail($validated['product_id']);
            $quantity = $validated['quantity'] ?? 1;

            $result = $paymentService->createProductPaymentIntent($user, $product, $quantity);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed to create payment intent'], 400);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('createProductPaymentIntent failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create payment intent'], 500);
        }
    }

    /**
     * Create investment payment intent (Stripe)
     */
    public function createInvestmentPaymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_id' => 'required|exists:businesses,id',
                'amount' => 'required|numeric|min:1',
            ]);

            $user = $request->user();
            $business = Business::findOrFail($validated['business_id']);
            $amount = (float) $validated['amount'];

            $result = $paymentService->createInvestmentPaymentIntent($user, $business, $amount);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed to create payment intent'], 400);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('createInvestmentPaymentIntent failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create payment intent'], 500);
        }
    }

    /**
     * Confirm payment (Stripe) and update transaction
     */
    public function confirmPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $result = $paymentService->confirmPayment($validated['payment_intent_id'], $request->user());

            if (!$result['success']) {
                $status = ($result['status'] ?? null) === 403 ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Payment not succeeded'], $status);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('confirmPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to confirm payment'], 500);
        }
    }

    // (duplicate constructor removed)

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:100',
                'currency' => 'required|string|in:usd,eur,gbp',
                'business_id' => 'required|exists:businesses,id',
                'metadata' => 'array',
            ]);

            $user = $request->user();
            $business = Business::findOrFail($request->business_id);
            $amount = round($request->amount / 100, 2);

            $result = $paymentService->createInvestmentPaymentIntent($user, $business, $amount);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to create payment intent',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id'],
                'transaction_id' => $result['transaction_id'] ?? null,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

                return response()->json([
                    'success' => false,
                'message' => 'Payment processing error',
                'error' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment intent creation error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process bank transfer payment
     */
    public function processBankTransfer(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $request->validate([
                'payment_intent' => 'required|string',
                'amount' => 'required|numeric|min:100',
                'business_id' => 'required|exists:businesses,id',
                'transfer_type' => 'required|string|in:bank_transfer,wire_transfer',
            ]);

            $user = $request->user();
            $business = Business::findOrFail($request->business_id);
            $amount = round($request->amount / 100, 2);

            $result = $paymentService->createOfflineInvestmentTransaction(
                $user,
                $business,
                $amount,
                $request->payment_intent,
                'bank_transfer',
                [
                    'payment_intent' => $request->payment_intent,
                    'transfer_type' => $request->transfer_type,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Bank transfer initiated',
                'transaction_id' => $result['transaction_id'],
                'investment_id' => $result['investment_id'],
            ]);

        } catch (\Exception $e) {
            Log::error('Bank transfer processing error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bank transfer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Paystack payment for product
     */
    public function createPaystackProductPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1',
            ]);
            $user = $request->user();
            $product = Product::findOrFail($validated['product_id']);
            $quantity = $validated['quantity'] ?? 1;
            $result = $paymentService->createPaystackProductPayment($user, $product, $quantity);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to init Paystack'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('createPaystackProductPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to init Paystack payment'], 500);
        }
    }

    /**
     * Create Paystack payment for investment
     */
    public function createPaystackInvestmentPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_id' => 'required|exists:businesses,id',
                'amount' => 'required|numeric|min:1',
            ]);
            $user = $request->user();
            $business = Business::findOrFail($validated['business_id']);
            $amount = (float) $validated['amount'];
            $result = $paymentService->createPaystackInvestmentPayment($user, $business, $amount);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to init Paystack'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('createPaystackInvestmentPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to init Paystack payment'], 500);
        }
    }

    /**
     * Verify Paystack payment via reference
     */
    public function verifyPaystackPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);
            $result = $paymentService->verifyPaystackPayment($validated['reference'], $request->user());
            if (!$result['success']) {
                $status = ($result['status'] ?? null) === 403 ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Verification failed'], $status);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('verifyPaystackPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to verify Paystack payment'], 500);
        }
    }

    /**
     * Get enabled payment gateways for checkout.
     */
    public function getAvailableGateways(PaymentGatewayService $gatewayService): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $gatewayService->getPublicGateways(),
        ]);
    }

    /**
     * Create Flutterwave payment for product
     */
    public function createFlutterwaveProductPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1',
            ]);
            $user = $request->user();
            $product = Product::findOrFail($validated['product_id']);
            $quantity = $validated['quantity'] ?? 1;
            $result = $paymentService->createFlutterwaveProductPayment($user, $product, $quantity);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to init Flutterwave'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('createFlutterwaveProductPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to init Flutterwave payment'], 500);
        }
    }

    /**
     * Create Flutterwave payment for investment
     */
    public function createFlutterwaveInvestmentPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_id' => 'required|exists:businesses,id',
                'amount' => 'required|numeric|min:1',
            ]);
            $user = $request->user();
            $business = Business::findOrFail($validated['business_id']);
            $amount = (float) $validated['amount'];
            $result = $paymentService->createFlutterwaveInvestmentPayment($user, $business, $amount);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to init Flutterwave'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('createFlutterwaveInvestmentPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to init Flutterwave payment'], 500);
        }
    }

    /**
     * Verify Flutterwave payment via reference
     */
    public function verifyFlutterwavePayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);
            $result = $paymentService->verifyFlutterwavePayment($validated['reference'], $request->user());
            if (!$result['success']) {
                $status = ($result['status'] ?? null) === 403 ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Verification failed'], $status);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('verifyFlutterwavePayment failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to verify Flutterwave payment'], 500);
        }
    }

    /**
     * Paystack redirect callback after customer pays.
     */
    public function paystackCallback(Request $request, PaymentService $paymentService, PaymentGatewayService $gatewayService)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect($gatewayService->getFrontendRedirectUrl('failed'));
        }

        $result = $paymentService->verifyPaystackPayment($reference);

        return redirect($gatewayService->getFrontendRedirectUrl(
            $result['success'] ? 'success' : 'failed',
            $reference
        ));
    }

    /**
     * Flutterwave redirect callback after customer pays.
     */
    public function flutterwaveCallback(Request $request, PaymentService $paymentService, PaymentGatewayService $gatewayService)
    {
        $reference = $request->query('reference')
            ?? $request->query('tx_ref')
            ?? $request->query('transaction_id');

        if (!$reference) {
            return redirect($gatewayService->getFrontendRedirectUrl('failed'));
        }

        $result = $paymentService->verifyFlutterwavePayment($reference);

        return redirect($gatewayService->getFrontendRedirectUrl(
            $result['success'] ? 'success' : 'failed',
            $reference
        ));
    }

    /**
     * Flutterwave webhook handler
     */
    public function handleFlutterwaveWebhook(Request $request, PaymentService $paymentService): JsonResponse
    {
        $signature = $request->header('verif-hash');
        $secret = app(PaymentGatewayService::class)->getWebhookSecret('flutterwave');

        if (!$secret) {
            Log::warning('Flutterwave webhook rejected: secret not configured');
            return response()->json(['success' => false, 'message' => 'Webhook not configured'], 503);
        }

        if ($signature !== $secret) {
            Log::warning('Flutterwave webhook signature mismatch');
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        try {
            $event = $request->all();
            if (($event['event'] ?? '') === 'charge.completed' && ($event['data']['status'] ?? '') === 'successful') {
                $reference = $event['data']['tx_ref'] ?? null;
                if ($reference) {
                    $paymentService->verifyFlutterwavePayment($reference);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Flutterwave webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Create subscription payment intent (Stripe)
     */
    public function createSubscriptionPaymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|string',
                'currency' => 'nullable|string|size:3',
            ]);

            $user = $request->user();
            $currency = strtoupper($validated['currency'] ?? 'USD');

            $result = $paymentService->createSubscriptionPaymentIntent($user, $validated['plan_id'], $currency);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed to create payment'], 400);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('createSubscriptionPaymentIntent failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create subscription payment'], 500);
        }
    }

    /**
     * Create featured listing promotion payment intent (Stripe)
     */
    public function createFeaturedListingPaymentIntent(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'listable_type' => 'nullable|in:product,business',
                'listable_id' => 'nullable|integer',
            ]);

            $user = $request->user();
            $meta = array_filter([
                'listable_type' => $validated['listable_type'] ?? null,
                'listable_id' => $validated['listable_id'] ?? null,
            ]);

            $result = $paymentService->createFeaturedListingPaymentIntent($user, (float) $validated['amount'], $meta);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed to create payment'], 400);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('createFeaturedListingPaymentIntent failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create featured listing payment'], 500);
        }
    }

    /**
     * Create escrow transaction
     */
    public function createEscrowTransaction(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_id' => 'required|exists:businesses,id',
                'amount' => 'required|numeric|min:1',
            ]);
            $buyer = $request->user();
            $business = Business::findOrFail($validated['business_id']);
            $amount = (float) $validated['amount'];
            $result = $paymentService->createEscrowTransaction($buyer, $business, $amount);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to create escrow'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('createEscrowTransaction failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create escrow'], 500);
        }
    }

    /**
     * Release escrow funds
     */
    public function listEscrowTransactions(Request $request, \App\Services\EscrowService $escrowService): JsonResponse
    {
        try {
            $escrows = $escrowService->listForUser(
                $request->user(),
                $request->get('status'),
                $request->get('role')
            );

            $escrows->setCollection(
                $escrows->getCollection()->map(fn ($tx) => $escrowService->format($tx))
            );

            return response()->json(['success' => true, 'data' => $escrows]);
        } catch (\Exception $e) {
            Log::error('listEscrowTransactions failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to load escrows'], 500);
        }
    }

    public function updateEscrowConditions(Request $request, string $escrow_id, \App\Services\EscrowService $escrowService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'conditions' => 'required|array',
            ]);

            $transaction = $escrowService->updateConditions($escrow_id, $request->user(), $validated['conditions']);

            return response()->json([
                'success' => true,
                'message' => 'Escrow milestones updated',
                'data' => $escrowService->format($transaction),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('updateEscrowConditions failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update escrow'], 500);
        }
    }

    /**
     * Release escrow funds
     */
    public function releaseEscrowFunds(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'escrow_id' => 'required|string',
                'conditions' => 'required|array',
            ]);
            $result = $paymentService->releaseEscrowFunds($validated['escrow_id'], $validated['conditions']);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Failed to release escrow'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('releaseEscrowFunds failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to release escrow'], 500);
        }
    }

    /**
     * Get escrow transaction by reference id
     */
    public function getEscrowTransaction(string $escrow_id, \App\Services\EscrowService $escrowService): JsonResponse
    {
        try {
            $transaction = $escrowService->findForUser($escrow_id, auth()->user());
            if (! $transaction) {
                return response()->json(['success' => false, 'message' => 'Escrow not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $escrowService->format($transaction->load(['buyer', 'seller'])),
            ]);
        } catch (\Exception $e) {
            Log::error('getEscrowTransaction failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to fetch escrow'], 500);
        }
    }

    /**
     * Refund endpoint that delegates to PaymentService
     */
    public function processRefund(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|exists:transactions,id',
                'amount' => 'nullable|numeric|min:0.01',
            ]);
            $transaction = Transaction::findOrFail($validated['transaction_id']);
            $amount = isset($validated['amount']) ? (float) $validated['amount'] : null;
            $result = $paymentService->processRefund($transaction, $amount);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Refund failed'], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('processRefund failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to process refund'], 500);
        }
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $balance = $paymentService->getWalletBalance($request->user());
            return response()->json(['success' => true, 'data' => ['balance' => $balance, 'currency' => 'USD']]);
        } catch (\Exception $e) {
            Log::error('getWalletBalance failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to get wallet balance'], 500);
        }
    }

    /**
     * Create Stripe Connect account for seller
     */
    public function createStripeAccount(Request $request, StripeConnectService $stripeConnect): JsonResponse
    {
        try {
            $result = $stripeConnect->startOnboarding(
                $request->user(),
                $request->input('return_url'),
                $request->input('refresh_url')
            );

            return response()->json(
                $result['success']
                    ? ['success' => true, 'data' => $result]
                    : ['success' => false, 'message' => $result['error'] ?? 'Failed to create account'],
                $result['success'] ? 200 : 400
            );
        } catch (\Exception $e) {
            Log::error('createStripeAccount failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create Stripe account'], 500);
        }
    }

    /**
     * Get Stripe Connect account status
     */
    public function getStripeAccountStatus(Request $request, StripeConnectService $stripeConnect): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $stripeConnect->getStatus($request->user()),
            ]);
        } catch (\Exception $e) {
            Log::error('getStripeAccountStatus failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to fetch Stripe account status'], 500);
        }
    }

    /**
     * Start Stripe Connect onboarding (creates account if needed)
     */
    public function startStripeConnectOnboarding(Request $request, StripeConnectService $stripeConnect): JsonResponse
    {
        return $this->createStripeAccount($request, $stripeConnect);
    }

    /**
     * Get Stripe Connect status for seller payouts
     */
    public function getStripeConnectStatus(Request $request, StripeConnectService $stripeConnect): JsonResponse
    {
        return $this->getStripeAccountStatus($request, $stripeConnect);
    }

    /**
     * Open Stripe Express dashboard for connected seller
     */
    public function getStripeConnectDashboard(Request $request, StripeConnectService $stripeConnect): JsonResponse
    {
        try {
            $result = $stripeConnect->createDashboardLink($request->user());

            return response()->json(
                $result['success']
                    ? ['success' => true, 'data' => $result]
                    : ['success' => false, 'message' => $result['error'] ?? 'Failed to open dashboard'],
                $result['success'] ? 200 : 400
            );
        } catch (\Exception $e) {
            Log::error('getStripeConnectDashboard failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to open Stripe dashboard'], 500);
        }
    }

    /**
     * Stripe webhook handler
     */
    public function handleWebhook(Request $request, PaymentService $paymentService): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = app(PaymentGatewayService::class)->getWebhookSecret('stripe');
        try {
            $event = StripeWebhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        try {
            if ($event->type === 'payment_intent.succeeded') {
                $intent = $event->data->object; // contains id
                $paymentService->confirmPayment($intent->id);
            } elseif ($event->type === 'account.updated') {
                app(StripeConnectService::class)->syncAccountFromWebhook($event->data->object);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', ['error' => $e->getMessage(), 'type' => $event->type ?? null]);
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Paystack webhook handler
     */
    public function handlePaystackWebhook(Request $request, PaymentService $paymentService): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');
        $secret = app(PaymentGatewayService::class)->getWebhookSecret('paystack');
        $computed = hash_hmac('sha512', $payload, $secret ?? '');
        if (!$secret || $computed !== $signature) {
            Log::warning('Paystack webhook signature mismatch');
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        try {
            $event = json_decode($payload, true);
            if (($event['event'] ?? '') === 'charge.success') {
                $reference = $event['data']['reference'] ?? null;
                if ($reference) {
                    $paymentService->verifyPaystackPayment($reference);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Process cryptocurrency payment
     */
    public function processCryptoPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $request->validate([
                'payment_intent' => 'required|string',
                'amount' => 'required|numeric|min:100',
                'business_id' => 'required|exists:businesses,id',
                'crypto_type' => 'required|string|in:bitcoin,ethereum,usdc',
            ]);

            $user = $request->user();
            $business = Business::findOrFail($request->business_id);
            $amount = round($request->amount / 100, 2);
            $referenceId = 'CRYPTO-' . time() . '-' . $user->id;

            $result = $paymentService->createOfflineInvestmentTransaction(
                $user,
                $business,
                $amount,
                $referenceId,
                'cryptocurrency',
                [
                    'payment_intent' => $request->payment_intent,
                    'crypto_type' => $request->crypto_type,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Cryptocurrency payment initiated',
                'transaction_id' => $result['transaction_id'],
                'investment_id' => $result['investment_id'],
            ]);

        } catch (\Exception $e) {
            Log::error('Crypto payment processing error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process cryptocurrency payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment methods for user
     */
    public function getPaymentMethods(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $result = $paymentService->listSavedPaymentMethods($request->user());

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to get payment methods',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'],
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment methods error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment methods',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save payment method
     */
    public function savePaymentMethod(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $request->validate([
                'payment_method_id' => 'required|string',
                'type' => 'required|string|in:card,bank_account',
            ]);

            $result = $paymentService->savePaymentMethod(
                $request->user(),
                $request->payment_method_id
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to save payment method',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully',
                'data' => $result['data'],
            ]);

        } catch (\Exception $e) {
            Log::error('Save payment method error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $transactions = Transaction::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
                ->orderBy('created_at', 'desc')
                ->paginate($request->integer('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Get transaction history error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentIntentId): JsonResponse
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

                return response()->json([
                    'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Get payment status error', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel payment
     */
    public function cancelPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
            $paymentIntent->cancel();

                return response()->json([
                    'success' => true,
                'message' => 'Payment cancelled successfully',
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment',
                'error' => $e->getMessage(),
                ], 400);

        } catch (\Exception $e) {
            Log::error('Cancel payment error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            // Accept either transaction_id (preferred) or payment_intent_id (legacy)
            $validated = $request->validate([
                'transaction_id' => 'nullable|exists:transactions,id',
                'payment_intent_id' => 'nullable|string',
                'amount' => 'nullable|numeric|min:0.01',
            ]);

            if (!empty($validated['transaction_id'])) {
                $transaction = Transaction::findOrFail($validated['transaction_id']);
                $amount = isset($validated['amount']) ? (float) $validated['amount'] : null;
                $result = $paymentService->processRefund($transaction, $amount);
                return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Refund failed'], $result['success'] ? 200 : 400);
            }

            // Legacy direct Stripe refund by payment_intent_id
            $legacyValidated = $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $refundData = [
                'payment_intent' => $legacyValidated['payment_intent_id'],
            ];

            if (!empty($validated['amount'])) {
                $refundData['amount'] = (int) round($validated['amount'] * 100); // cents
            }

            $refund = \Stripe\Refund::create($refundData);

            return response()->json([
                'success' => true,
                'message' => 'Payment refunded successfully',
                'refund_id' => $refund->id,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to refund payment', 'error' => $e->getMessage()], 400);

        } catch (\Exception $e) {
            Log::error('Refund payment error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to refund payment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment analytics
     */
    public function getPaymentAnalytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $userId = $user->id;

            $totalInvested = Investment::where('investor_id', $userId)
                ->where('status', 'completed')
                ->sum('amount');

            $completedInvestments = Investment::where('investor_id', $userId)
                ->where('status', 'completed')
                ->count();

            $totalTransactions = Transaction::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('buyer_id', $userId);
            })->count();

            $successfulTransactions = Transaction::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('buyer_id', $userId);
            })
                ->where('status', 'completed')
                ->count();

            $successRate = $totalTransactions > 0
                ? round(($successfulTransactions / $totalTransactions) * 100, 2)
                : 0;

            $analytics = [
                'total_invested' => $totalInvested,
                'total_transactions' => $totalTransactions,
                'successful_transactions' => $successfulTransactions,
                'success_rate' => $successRate,
                'average_investment' => $completedInvestments > 0
                    ? round($totalInvested / $completedInvestments, 2)
                    : 0,
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment analytics error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}