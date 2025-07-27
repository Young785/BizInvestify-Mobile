<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Product;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create payment intent for product purchase
     */
    public function createProductPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            $user = $request->user();
            $quantity = $request->quantity ?? 1;

            // Check if product is available
            if ($product->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available for purchase'
                ], 400);
            }

            // Check if user is trying to buy their own product
            if ($product->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot purchase your own product'
                ], 400);
            }

            $result = $this->paymentService->createProductPaymentIntent(
                $user,
                $product,
                $quantity
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'client_secret' => $result['client_secret'],
                        'payment_intent_id' => $result['payment_intent_id'],
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Product payment intent creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment setup failed'
            ], 500);
        }
    }

    /**
     * Create payment intent for business investment
     */
    public function createInvestmentPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $business = Business::findOrFail($request->business_id);
            $user = $request->user();
            $amount = $request->amount;

            // Check if business is available for investment
            if ($business->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business is not available for investment'
                ], 400);
            }

            // Check if user is trying to invest in their own business
            if ($business->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot invest in your own business'
                ], 400);
            }

            $result = $this->paymentService->createInvestmentPaymentIntent(
                $user,
                $business,
                $amount
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'client_secret' => $result['client_secret'],
                        'payment_intent_id' => $result['payment_intent_id'],
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Investment payment intent creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Investment payment setup failed'
            ], 500);
        }
    }

    /**
     * Confirm payment
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->paymentService->confirmPayment($request->payment_intent_id);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ],
                    'message' => 'Payment confirmed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed'
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'nullable|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::findOrFail($request->transaction_id);
            $user = $request->user();

            // Check if user owns the transaction
            if ($transaction->user_id !== $user->id && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to refund this transaction'
                ], 403);
            }

            // Check if transaction can be refunded
            if ($transaction->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction cannot be refunded'
                ], 400);
            }

            $result = $this->paymentService->processRefund(
                $transaction,
                $request->amount
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'refund_id' => $result['refund_id'],
                        'refund_transaction_id' => $result['refund_transaction_id'],
                        'amount' => $result['amount']
                    ],
                    'message' => 'Refund processed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed'
            ], 500);
        }
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $balance = $this->paymentService->getWalletBalance($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $balance,
                    'currency' => 'USD'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Wallet balance retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wallet balance'
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
            $limit = $request->get('limit', 20);
            
            $result = $this->paymentService->getTransactionHistory($user, $limit);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total']
            ]);

        } catch (\Exception $e) {
            Log::error('Transaction history retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history'
            ], 500);
        }
    }

    /**
     * Create Stripe Connect account
     */
    public function createStripeAccount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user already has a Stripe account
            if ($user->stripe_account_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a Stripe account'
                ], 400);
            }

            $result = $this->paymentService->createConnectAccount($user);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'account_id' => $result['account_id'],
                        'onboarding_url' => $result['onboarding_url']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Stripe account creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Stripe account'
            ], 500);
        }
    }

    /**
     * Get Stripe account status
     */
    public function getStripeAccountStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->paymentService->getConnectAccountStatus($user);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'account_id' => $result['account_id'],
                        'status' => $result['status'],
                        'charges_enabled' => $result['charges_enabled'],
                        'payouts_enabled' => $result['payouts_enabled'],
                        'requirements' => $result['requirements']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Stripe account status check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check Stripe account status'
            ], 500);
        }
    }

    /**
     * Webhook handler for Stripe events
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $endpointSecret = config('services.stripe.webhook_secret');

            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;
                case 'account.updated':
                    $this->handleAccountUpdated($event->data->object);
                    break;
                default:
                    Log::info('Unhandled Stripe event: ' . $event->type);
            }

            return response()->json(['success' => true]);

        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 500);
        }
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        try {
            $this->paymentService->confirmPayment($paymentIntent->id);
        } catch (\Exception $e) {
            Log::error('Payment intent succeeded handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent): void
    {
        try {
            $transaction = Transaction::where('payment_intent_id', $paymentIntent->id)->first();
            if ($transaction) {
                $transaction->update(['status' => 'failed']);
            }
        } catch (\Exception $e) {
            Log::error('Payment intent failed handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle account updates
     */
    private function handleAccountUpdated($account): void
    {
        try {
            $user = User::where('stripe_account_id', $account->id)->first();
            if ($user) {
                // Update user's Stripe account status if needed
                Log::info('Stripe account updated for user: ' . $user->id);
            }
        } catch (\Exception $e) {
            Log::error('Account update handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Paystack payment for product purchase
     */
    public function createPaystackProductPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            $user = $request->user();
            $quantity = $request->quantity ?? 1;

            // Check if product is available
            if ($product->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available for purchase'
                ], 400);
            }

            // Check if user is trying to buy their own product
            if ($product->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot purchase your own product'
                ], 400);
            }

            $result = $this->paymentService->createPaystackProductPayment(
                $user,
                $product,
                $quantity
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'authorization_url' => $result['authorization_url'],
                        'reference' => $result['reference'],
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Paystack product payment creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment setup failed'
            ], 500);
        }
    }

    /**
     * Create Paystack payment for business investment
     */
    public function createPaystackInvestmentPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $business = Business::findOrFail($request->business_id);
            $user = $request->user();
            $amount = $request->amount;

            // Check if business is available for investment
            if ($business->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business is not available for investment'
                ], 400);
            }

            // Check if user is trying to invest in their own business
            if ($business->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot invest in your own business'
                ], 400);
            }

            $result = $this->paymentService->createPaystackInvestmentPayment(
                $user,
                $business,
                $amount
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'authorization_url' => $result['authorization_url'],
                        'reference' => $result['reference'],
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Paystack investment payment creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Investment payment setup failed'
            ], 500);
        }
    }

    /**
     * Verify Paystack payment
     */
    public function verifyPaystackPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->paymentService->verifyPaystackPayment($request->reference);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount']
                    ],
                    'message' => 'Payment verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Paystack payment verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ], 500);
        }
    }

    /**
     * Create escrow transaction for business acquisition
     */
    public function createEscrowTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $business = Business::findOrFail($request->business_id);
            $user = $request->user();
            $amount = $request->amount;

            // Check if business is available for acquisition
            if ($business->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business is not available for acquisition'
                ], 400);
            }

            // Check if user is trying to buy their own business
            if ($business->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot acquire your own business'
                ], 400);
            }

            $result = $this->paymentService->createEscrowTransaction(
                $user,
                $business,
                $amount
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'escrow_id' => $result['escrow_id'],
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $result['amount'],
                        'status' => $result['status']
                    ],
                    'message' => 'Escrow transaction created successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Escrow transaction creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Escrow transaction creation failed'
            ], 500);
        }
    }

    /**
     * Release escrow funds
     */
    public function releaseEscrowFunds(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'escrow_id' => 'required|string',
            'conditions' => 'required|array'
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
            
            // Check if user has permission to release escrow (admin or involved party)
            $transaction = Transaction::where('reference_id', $request->escrow_id)->first();
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escrow transaction not found'
                ], 404);
            }

            $metadata = $transaction->metadata;
            $isInvolvedParty = $transaction->user_id === $user->id || $metadata['seller_id'] === $user->id;
            $isAdmin = $user->hasRole(['admin', 'super_admin']);

            if (!$isInvolvedParty && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to release escrow funds'
                ], 403);
            }

            $result = $this->paymentService->releaseEscrowFunds(
                $request->escrow_id,
                $request->conditions
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'escrow_id' => $result['escrow_id'],
                        'amount' => $result['amount'],
                        'released_at' => $result['released_at']
                    ],
                    'message' => 'Escrow funds released successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Escrow funds release failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Escrow funds release failed'
            ], 500);
        }
    }

    /**
     * Get escrow transaction details
     */
    public function getEscrowTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'escrow_id' => 'required|string'
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
            $transaction = Transaction::where('reference_id', $request->escrow_id)
                ->where('type', 'escrow')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escrow transaction not found'
                ], 404);
            }

            $metadata = $transaction->metadata;
            $isInvolvedParty = $transaction->user_id === $user->id || $metadata['seller_id'] === $user->id;
            $isAdmin = $user->hasRole(['admin', 'super_admin']);

            if (!$isInvolvedParty && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view escrow transaction'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'escrow_id' => $transaction->reference_id,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                    'metadata' => $metadata
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Escrow transaction retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve escrow transaction'
            ], 500);
        }
    }

    /**
     * Paystack webhook handler
     */
    public function handlePaystackWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-Paystack-Signature');
            $secret = config('services.paystack.secret');

            // Verify webhook signature
            $computedSignature = hash_hmac('sha512', $payload, $secret);
            
            if ($signature !== $computedSignature) {
                Log::error('Invalid Paystack webhook signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $event = json_decode($payload, true);

            // Handle the event
            switch ($event['event']) {
                case 'charge.success':
                    $this->handlePaystackChargeSuccess($event['data']);
                    break;
                case 'transfer.success':
                    $this->handlePaystackTransferSuccess($event['data']);
                    break;
                default:
                    Log::info('Unhandled Paystack event: ' . $event['event']);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Paystack webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 500);
        }
    }

    /**
     * Handle successful Paystack charge
     */
    private function handlePaystackChargeSuccess($data): void
    {
        try {
            $reference = $data['reference'];
            $this->paymentService->verifyPaystackPayment($reference);
        } catch (\Exception $e) {
            Log::error('Paystack charge success handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful Paystack transfer
     */
    private function handlePaystackTransferSuccess($data): void
    {
        try {
            // Handle successful transfer to seller
            Log::info('Paystack transfer successful: ' . $data['reference']);
        } catch (\Exception $e) {
            Log::error('Paystack transfer success handling failed: ' . $e->getMessage());
        }
    }
} 