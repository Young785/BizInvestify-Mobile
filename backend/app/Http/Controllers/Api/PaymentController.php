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

            $result = $paymentService->confirmPayment($validated['payment_intent_id']);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Payment not succeeded'], 400);
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
    public function createPaymentIntent(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:100', // Minimum $1.00
                'currency' => 'required|string|in:usd,eur,gbp',
                'business_id' => 'required|exists:businesses,id',
                'metadata' => 'array',
            ]);

            $user = $request->user();
            $business = Business::findOrFail($request->business_id);

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => $request->currency,
                'metadata' => array_merge($request->metadata ?? [], [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'investment_type' => 'equity',
                ]),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

                return response()->json([
                    'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
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
    public function processBankTransfer(Request $request): JsonResponse
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

            // Create transaction record aligned with schema
            $transaction = Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $business->seller_id,
                'listing_id' => $business->id,
                'listing_type' => 'business',
                'amount' => $request->amount,
                'payment_method' => 'bank_transfer',
                'status' => 'pending',
                'payment_details' => [
                    'payment_intent' => $request->payment_intent,
                    'transfer_type' => $request->transfer_type,
                ],
            ]);

            // Create investment record
            $investment = Investment::create([
                'investor_id' => $user->id,
                'business_id' => $business->id,
                'amount' => $request->amount,
                'equity_percentage' => $this->calculateEquityPercentage($request->amount, $business),
                'status' => 'pending',
            ]);

                return response()->json([
                    'success' => true,
                'message' => 'Bank transfer initiated',
                'transaction_id' => $transaction->id,
                'investment_id' => $investment->id,
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
            $result = $paymentService->verifyPaystackPayment($validated['reference']);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Verification failed'], $result['success'] ? 200 : 400);
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
            $result = $paymentService->verifyFlutterwavePayment($validated['reference']);
            return response()->json($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'message' => $result['error'] ?? 'Verification failed'], $result['success'] ? 200 : 400);
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

        if ($secret && $signature !== $secret) {
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
    public function processCryptoPayment(Request $request): JsonResponse
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

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'amount' => $request->amount,
                'type' => 'investment',
                'status' => 'pending',
                'payment_method' => 'cryptocurrency',
                'reference' => 'CRYPTO-' . time(),
                'metadata' => [
                    'payment_intent' => $request->payment_intent,
                    'crypto_type' => $request->crypto_type,
                ],
            ]);

            // Create investment record
            $investment = Investment::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'amount' => $request->amount,
                'equity_percentage' => $this->calculateEquityPercentage($request->amount, $business),
                'status' => 'pending',
                'transaction_id' => $transaction->id,
            ]);

                return response()->json([
                    'success' => true,
                'message' => 'Cryptocurrency payment initiated',
                'transaction_id' => $transaction->id,
                'investment_id' => $investment->id,
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
    public function getPaymentMethods(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get saved payment methods from Stripe
            $paymentMethods = [];
            
            // In a real implementation, you would fetch saved payment methods from Stripe
            // For now, return empty array
            $paymentMethods = [];

                return response()->json([
                    'success' => true,
                'data' => $paymentMethods,
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
    public function savePaymentMethod(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_method_id' => 'required|string',
                'type' => 'required|string|in:card,bank_account',
            ]);

            $user = $request->user();

            // In a real implementation, you would save the payment method to Stripe
            // For now, just return success

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully',
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
            
            $transactions = Transaction::where('user_id', $user->id)
                ->with(['business', 'investment'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

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
            
            // Get analytics data
            $totalInvested = Investment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount');

            $totalTransactions = Transaction::where('user_id', $user->id)->count();

            $successfulTransactions = Transaction::where('user_id', $user->id)
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
                'average_investment' => $totalTransactions > 0 
                    ? round($totalInvested / $totalTransactions, 2) 
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

    /**
     * Calculate equity percentage based on investment amount and business valuation
     */
    private function calculateEquityPercentage(float $amount, Business $business): float
    {
        if ($business->valuation <= 0) {
            return 0;
        }

        return round(($amount / $business->valuation) * 100, 4);
    }
} 