<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Business;
use App\Models\Investment;
use App\Models\FeaturedListing;
use App\Models\Withdrawal;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    public function __construct(
        private PaymentGatewayService $gateways,
        private PlatformSettingsService $platformSettings,
    ) {
    }

    private function ensureStripeConfigured(): bool
    {
        $secret = $this->gateways->getSecretKey('stripe');
        if (!$secret) {
            return false;
        }
        Stripe::setApiKey($secret);

        return true;
    }

    /**
     * Create a payment intent for product purchase
     */
    public function createProductPaymentIntent(User $user, Product $product, int $quantity = 1): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->ensureStripeConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not enabled or configured'];
        }

        try {
            $amount = $product->price * $quantity * 100; // Convert to cents

            // Create or get Stripe customer
            $customer = $this->getOrCreateCustomer($user);

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'product_purchase',
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $amountDollars = $product->price * $quantity;

            // Create transaction record
            $transaction = $this->createPaymentTransaction([
                'user_id' => $user->id,
                'type' => 'purchase',
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'listing_id' => $product->id,
                'listing_type' => 'product',
                'metadata' => [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'product_purchase',
                    'stripe_payment_intent_id' => $paymentIntent->id
                ]
            ], $amountDollars, 'stripe', 'sale', $user->id, $product->seller_id);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amountDollars,
                'commission' => $transaction->commission,
                'fee' => $transaction->commission,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment intent for business investment
     */
    public function createInvestmentPaymentIntent(User $user, Business $business, float $amount): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->ensureStripeConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not enabled or configured'];
        }

        try {
            $amountInCents = $amount * 100; // Convert to cents

            // Validate investment amount
            if ($amount > $business->funding_goal) {
                return [
                    'success' => false,
                    'error' => 'Investment amount exceeds funding goal'
                ];
            }

            // Create or get Stripe customer
            $customer = $this->getOrCreateCustomer($user);

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'type' => 'business_investment',
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create transaction record
            $transaction = $this->createPaymentTransaction([
                'user_id' => $user->id,
                'type' => 'investment',
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'listing_id' => $business->id,
                'listing_type' => 'business',
                'metadata' => [
                    'business_id' => $business->id,
                    'type' => 'business_investment',
                    'stripe_payment_intent_id' => $paymentIntent->id
                ]
            ], $amount, 'stripe', 'investment', $user->id, $business->user_id);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'commission' => $transaction->commission,
                'fee' => $transaction->commission,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe investment payment intent creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Paystack payment for product purchase
     */
    public function createPaystackProductPayment(User $user, Product $product, int $quantity = 1): array
    {
        try {
            $amount = $product->price * $quantity * 100; // Convert to kobo (Paystack uses kobo)

            $url = "https://api.paystack.co/transaction/initialize";
            $paystackConfig = $this->gateways->getGateway('paystack');
            $secret = $this->gateways->getSecretKey('paystack');
            if (!$this->gateways->isEnabled('paystack') || !$secret) {
                return ['success' => false, 'error' => 'Paystack is not configured'];
            }

            $headers = [
                "Authorization: Bearer " . $secret,
                "Cache-Control: no-cache",
            ];

            $currency = $this->gateways->getPrimaryCurrency('paystack');

            $fields = [
                'email' => $user->email,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => 'BIZ_' . time() . '_' . $user->id,
                'callback_url' => $this->gateways->getCallbackUrl('paystack'),
                'metadata' => [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'product_purchase'
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($result['status']) {
                $amountDollars = $amount / 100;
                $transaction = $this->createPaymentTransaction([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'currency' => strtolower($currency),
                    'status' => 'pending',
                    'payment_method' => 'paystack',
                    'reference_id' => $result['data']['reference'],
                    'listing_id' => $product->id,
                    'listing_type' => 'product',
                    'metadata' => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'type' => 'product_purchase',
                        'paystack_reference' => $result['data']['reference'],
                        'authorization_url' => $result['data']['authorization_url']
                    ]
                ], $amountDollars, 'paystack', 'sale', $user->id, $product->seller_id);

                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                    'transaction_id' => $transaction->id,
                    'amount' => $amountDollars,
                    'commission' => $transaction->commission,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Paystack payment initialization failed'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Paystack payment creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Paystack payment for business investment
     */
    public function createPaystackInvestmentPayment(User $user, Business $business, float $amount): array
    {
        try {
            $amountInKobo = $amount * 100; // Convert to kobo

            // Validate investment amount
            if ($amount > $business->funding_goal) {
                return [
                    'success' => false,
                    'error' => 'Investment amount exceeds funding goal'
                ];
            }

            $url = "https://api.paystack.co/transaction/initialize";
            $paystackConfig = $this->gateways->getGateway('paystack');
            $secret = $this->gateways->getSecretKey('paystack');
            if (!$this->gateways->isEnabled('paystack') || !$secret) {
                return ['success' => false, 'error' => 'Paystack is not configured'];
            }

            $headers = [
                "Authorization: Bearer " . $secret,
                "Cache-Control: no-cache",
            ];

            $currency = $this->gateways->getPrimaryCurrency('paystack');

            $fields = [
                'email' => $user->email,
                'amount' => $amountInKobo,
                'currency' => $currency,
                'reference' => 'BIZ_INV_' . time() . '_' . $user->id,
                'callback_url' => $this->gateways->getCallbackUrl('paystack'),
                'metadata' => [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'type' => 'business_investment'
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($result['status']) {
                // Create transaction record
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'investment',
                    'amount' => $amount,
                    'currency' => 'ngn',
                    'status' => 'pending',
                    'payment_method' => 'paystack',
                    'reference_id' => $result['data']['reference'],
                    'metadata' => [
                        'business_id' => $business->id,
                        'paystack_reference' => $result['data']['reference'],
                        'authorization_url' => $result['data']['authorization_url']
                    ]
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                    'transaction_id' => $transaction->id,
                    'amount' => $amount
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Paystack payment initialization failed'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Paystack investment payment creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify Paystack payment
     */
    public function verifyPaystackPayment(string $reference): array
    {
        try {
            $url = "https://api.paystack.co/transaction/verify/" . $reference;
            $paystackConfig = $this->gateways->getGateway('paystack');
            $secret = $this->gateways->getSecretKey('paystack');
            if (!$this->gateways->isEnabled('paystack') || !$secret) {
                return ['success' => false, 'error' => 'Paystack is not configured'];
            }

            $headers = [
                "Authorization: Bearer " . $secret,
                "Cache-Control: no-cache",
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (!is_array($result)) {
                return ['success' => false, 'error' => 'Invalid response from Paystack'];
            }

            if (($result['status'] ?? false) && ($result['data']['status'] ?? '') === 'success') {
                // Find and update transaction
                $transaction = Transaction::where('reference_id', $reference)->first();
                
                if (!$transaction) {
                    return [
                        'success' => false,
                        'error' => 'Transaction not found'
                    ];
                }

                $transaction->update([
                    'status' => 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'paystack_transaction_id' => $result['data']['id'],
                        'confirmed_at' => now()->toISOString()
                    ])
                ]);

                // Handle post-payment logic
                $this->handlePostPayment($transaction, (object) $result['data']['metadata']);

                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create escrow transaction for business acquisition
     */
    public function createEscrowTransaction(User $buyer, Business $business, float $amount): array
    {
        try {
            if (!$this->gateways->isEnabled('stripe') || !$this->ensureStripeConfigured()) {
                return ['success' => false, 'error' => 'Stripe is required for escrow payments'];
            }

            $commission = $this->calculateCommission($amount, 'investment', 'stripe');
            $customer = $this->getOrCreateCustomer($buyer);
            $amountInCents = (int) round($amount * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $buyer->id,
                    'business_id' => $business->id,
                    'seller_id' => $business->seller_id,
                    'type' => 'escrow',
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $transaction = $this->createPaymentTransaction([
                'user_id' => $buyer->id,
                'type' => 'escrow',
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'listing_id' => $business->id,
                'listing_type' => 'business',
                'metadata' => [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'seller_id' => $business->seller_id,
                    'escrow_type' => 'business_acquisition',
                    'type' => 'escrow',
                    'escrow_status' => 'pending_payment',
                    'release_conditions' => app(EscrowService::class)->defaultConditions(),
                ],
            ], $amount, 'stripe', 'investment', $buyer->id, $business->seller_id);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'escrow_id' => $transaction->reference_id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'commission' => $commission,
                'fee' => $commission,
                'status' => 'pending',
            ];
        } catch (\Exception $e) {
            Log::error('Escrow transaction creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Release escrow funds
     */
    public function releaseEscrowFunds(string $escrowId, array $conditions): array
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return ['success' => false, 'error' => 'Unauthorized'];
            }

            return app(EscrowService::class)->release($escrowId, $user, $conditions);
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Escrow funds release failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Transfer funds to seller
     */
    private function transferFundsToSeller(int $sellerId, float $amount, int $transactionId): void
    {
        // Create seller payout transaction
        Transaction::create([
            'user_id' => $sellerId,
            'type' => 'payout',
            'amount' => $amount,
            'currency' => 'usd',
            'status' => 'completed',
            'payment_method' => 'transfer',
            'reference_id' => 'PAYOUT_' . time() . '_' . $sellerId,
            'metadata' => [
                'source_transaction_id' => $transactionId,
                'payout_type' => 'escrow_release'
            ]
        ]);
    }

    /**
     * Confirm payment and update transaction status
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        if (!$this->ensureStripeConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                // Find and update transaction
                $transaction = Transaction::where('reference_id', $paymentIntentId)->first();
                
                if (!$transaction) {
                    return [
                        'success' => false,
                        'error' => 'Transaction not found'
                    ];
                }

                $transaction->update([
                    'status' => $transaction->type === 'escrow' ? 'processing' : 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'stripe_charge_id' => $paymentIntent->latest_charge,
                        'confirmed_at' => now()->toISOString(),
                    ]),
                ]);

                if ($transaction->type === 'escrow') {
                    app(EscrowService::class)->markFunded($transaction->fresh());
                }

                // Handle post-payment logic based on transaction type
                $this->handlePostPayment($transaction, $paymentIntent);

                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment not succeeded'
            ];

        } catch (ApiErrorException $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Transaction $transaction, float $amount = null): array
    {
        try {
            $refundAmount = $amount ?? $transaction->amount;
            
            if ($transaction->payment_method === 'stripe') {
                // Create refund in Stripe
                $refund = \Stripe\Refund::create([
                    'payment_intent' => $transaction->reference_id,
                    'amount' => $refundAmount * 100, // Convert to cents
                ]);

                $refundReference = $refund->id;
            } elseif ($transaction->payment_method === 'paystack') {
                // Process Paystack refund
                $url = "https://api.paystack.co/refund";
                $paystackConfig = $this->gateways->getGateway('paystack');
                $headers = [
                    "Authorization: Bearer " . ($paystackConfig['secret_key'] ?? ''),
                    "Cache-Control: no-cache",
                ];

                $fields = [
                    'transaction' => $transaction->metadata['paystack_transaction_id'] ?? '',
                    'amount' => $refundAmount * 100, // Convert to kobo
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);
                
                if (!$result['status']) {
                    throw new \Exception('Paystack refund failed: ' . ($result['message'] ?? 'Unknown error'));
                }

                $refundReference = $result['data']['reference'];
            } else {
                return [
                    'success' => false,
                    'error' => 'Unsupported payment method for refund'
                ];
            }

            // Create refund transaction
            $refundTransaction = Transaction::create([
                'user_id' => $transaction->user_id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'currency' => $transaction->currency,
                'status' => 'completed',
                'payment_method' => $transaction->payment_method,
                'reference_id' => $refundReference,
                'metadata' => [
                    'original_transaction_id' => $transaction->id,
                    'refund_reference' => $refundReference
                ]
            ]);

            if ($transaction->status === 'completed') {
                $transaction->update(['status' => 'refunded']);
            }

            $buyer = User::find($transaction->user_id);
            if ($buyer) {
                $currency = strtoupper((string) ($transaction->currency ?: 'USD'));
                app(NotificationService::class)->notifyWithEmail(
                    $buyer,
                    'refund',
                    'Refund Processed',
                    'A refund of '.$currency.' '.number_format($refundAmount, 2).' has been processed to your original payment method.',
                    [
                        'transaction_id' => $refundTransaction->id,
                        'original_transaction_id' => $transaction->id,
                        'action_url' => '/dashboard/transactions',
                        'action_label' => 'View Refund',
                        'email_details' => [
                            'Amount' => $currency.' '.number_format($refundAmount, 2),
                            'Reference' => $refundReference,
                        ],
                    ],
                    'high',
                    'Your refund has been processed'
                );
            }

            return [
                'success' => true,
                'refund_id' => $refundReference,
                'refund_transaction_id' => $refundTransaction->id,
                'amount' => $refundAmount
            ];

        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get or create Stripe customer
     */
    private function getOrCreateCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            try {
                return Customer::retrieve($user->stripe_customer_id);
            } catch (ApiErrorException $e) {
                // Customer not found, create new one
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'metadata' => [
                'user_id' => $user->id
            ]
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Create Stripe Connect account for sellers
     */
    public function createConnectAccount(User $user): array
    {
        return app(StripeConnectService::class)->startOnboarding($user);
    }

    /**
     * Get Connect account status
     */
    public function getConnectAccountStatus(User $user): array
    {
        $status = app(StripeConnectService::class)->getStatus($user);

        return array_merge(['success' => true], $status);
    }

    /**
     * Handle post-payment logic
     */
    private function handlePostPayment(Transaction $transaction, $metadata): void
    {
        $type = is_object($metadata) ? ($metadata->type ?? null) : ($metadata['type'] ?? null);
        if (!$type) {
            $type = $transaction->metadata['type'] ?? $transaction->type;
        }

        if ($type === 'product_purchase') {
            $this->handleProductPurchase($transaction, $metadata);
        } elseif ($type === 'business_investment') {
            $this->handleBusinessInvestment($transaction, $metadata);
        } elseif ($type === 'subscription') {
            $this->handleSubscriptionPayment($transaction, $metadata);
        } elseif ($type === 'featured_listing') {
            $this->handleFeaturedListingPayment($transaction, $metadata);
        } elseif ($type === 'escrow') {
            // Funded state handled in confirmPayment via EscrowService::markFunded
        }
    }

    /**
     * Handle product purchase completion
     */
    private function handleProductPurchase(Transaction $transaction, $metadata): void
    {
        $meta = $this->parsePaymentMetadata($metadata);
        $productId = $meta['product_id'] ?? $transaction->listing_id ?? null;
        $quantity = (int) ($meta['quantity'] ?? 1);

        $product = $productId ? Product::find($productId) : null;
        if ($product && $product->inventory_count !== null) {
            $product->decrement('inventory_count', $quantity);
        }

        $sellerId = (int) $transaction->seller_id;
        if ($sellerId > 0) {
            $this->creditSellerPayout($transaction, $sellerId, (float) $transaction->net_amount, 'product_sale');
        }

        $this->sendProductPurchaseNotifications($transaction, $product, $quantity);
    }

    private function sendProductPurchaseNotifications(Transaction $transaction, ?Product $product, int $quantity): void
    {
        $notificationService = app(NotificationService::class);
        $buyerId = (int) ($transaction->buyer_id ?: $transaction->user_id);
        $sellerId = (int) $transaction->seller_id;
        $amount = (float) $transaction->amount;
        $currency = strtoupper((string) ($transaction->currency ?: 'USD'));
        $productName = $product?->title ?? 'your purchase';
        $emailDetails = [
            'Product' => $productName,
            'Amount' => $currency.' '.number_format($amount, 2),
            'Quantity' => (string) $quantity,
        ];

        $buyer = User::find($buyerId);
        if ($buyer) {
            $notificationService->notifyWithEmail(
                $buyer,
                'transaction',
                'Purchase Confirmed',
                'Your purchase of '.$productName.' has been confirmed.',
                [
                    'transaction_id' => $transaction->id,
                    'product_id' => $product?->id,
                    'action_url' => '/dashboard/transactions',
                    'action_label' => 'View Transaction',
                    'email_details' => $emailDetails,
                ],
                'high',
                'Purchase confirmed: '.$productName
            );
        }

        $seller = $sellerId > 0 ? User::find($sellerId) : null;
        if ($seller) {
            $notificationService->notifyWithEmail(
                $seller,
                'payment_received',
                'New Sale Received',
                'You received a new sale for '.$productName.'.',
                [
                    'transaction_id' => $transaction->id,
                    'product_id' => $product?->id,
                    'action_url' => '/dashboard/transactions',
                    'action_label' => 'View Transaction',
                    'email_details' => $emailDetails,
                ],
                'high',
                'New sale: '.$productName
            );
        }
    }

    /**
     * Handle business investment completion
     */
    private function handleBusinessInvestment(Transaction $transaction, $metadata): void
    {
        $meta = $this->parsePaymentMetadata($metadata);
        $businessId = $meta['business_id'] ?? $transaction->listing_id ?? null;
        $investorId = (int) ($transaction->buyer_id ?: $transaction->user_id);

        if (! $businessId || ! $investorId) {
            Log::warning('Investment completion missing business or investor', [
                'transaction_id' => $transaction->id,
            ]);

            return;
        }

        $business = Business::find($businessId);
        if (! $business) {
            return;
        }

        $amount = (float) $transaction->amount;
        $equityPercentage = $this->calculateEquityPercentage($amount, $business);

        $investment = Investment::query()
            ->where('investor_id', $investorId)
            ->where('business_id', $business->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($investment) {
            $investment->update([
                'amount' => $amount,
                'equity_percentage' => $equityPercentage,
                'status' => 'completed',
            ]);
        } else {
            Investment::create([
                'investor_id' => $investorId,
                'business_id' => $business->id,
                'amount' => $amount,
                'equity_percentage' => $equityPercentage,
                'status' => 'completed',
                'message' => 'Investment completed via payment',
            ]);
        }

        $business->increment('funded_amount', $amount);

        if ($business->funding_goal > 0 && $business->funded_amount >= $business->funding_goal) {
            $business->update(['status' => 'funded']);
        }

        $sellerId = (int) ($transaction->seller_id ?: $business->seller_id);
        $payoutAmount = (float) ($transaction->net_amount ?: $amount);
        if ($sellerId > 0 && $payoutAmount > 0) {
            $this->creditSellerPayout($transaction, $sellerId, $payoutAmount, 'investment');
        }

        Notification::createNotification(
            $investorId,
            'investment_received',
            'Investment Confirmed',
            'Your investment in '.$business->name.' has been confirmed.',
            [
                'business_id' => $business->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'action_url' => '/dashboard/investments',
            ],
            'high'
        );

        if ($sellerId > 0) {
            Notification::createNotification(
                $sellerId,
                'payment_received',
                'New Investment Received',
                'You received a new investment in '.$business->name.'.',
                [
                    'business_id' => $business->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'action_url' => '/dashboard/investments',
                ],
                'high'
            );
        }

        $this->sendInvestmentConfirmationEmails($transaction, $business, $amount, $investorId, $sellerId);
    }

    private function sendInvestmentConfirmationEmails(
        Transaction $transaction,
        Business $business,
        float $amount,
        int $investorId,
        int $sellerId
    ): void {
        $notificationService = app(NotificationService::class);
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $currency = $transaction->currency ?: 'USD';

        $investor = User::find($investorId);
        if ($investor && $notificationService->shouldSendEmail($investor, 'investment_received')) {
            try {
                Mail::send('emails.investment-confirmation', [
                    'user' => $investor,
                    'business' => $business,
                    'amount' => $amount,
                    'currency' => $currency,
                    'role' => 'investor',
                    'dashboard_url' => $frontendUrl.'/dashboard/investments',
                ], function ($message) use ($investor, $business) {
                    $message->to($investor->email, $investor->name)
                        ->subject('Investment confirmed: '.$business->name);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to send investor investment email', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $investorId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $seller = $sellerId > 0 ? User::find($sellerId) : null;
        if ($seller && $notificationService->shouldSendEmail($seller, 'payment_received')) {
            try {
                Mail::send('emails.investment-confirmation', [
                    'user' => $seller,
                    'business' => $business,
                    'amount' => $amount,
                    'currency' => $currency,
                    'role' => 'seller',
                    'dashboard_url' => $frontendUrl.'/dashboard/investments',
                ], function ($message) use ($seller, $business) {
                    $message->to($seller->email, $seller->name)
                        ->subject('New investment received: '.$business->name);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to send seller investment email', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $sellerId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function creditSellerPayout(Transaction $source, int $sellerId, float $amount, string $payoutType): void
    {
        if ($sellerId <= 0 || $amount <= 0) {
            return;
        }

        $seller = User::find($sellerId);
        $transferMeta = [
            'source_transaction_id' => (string) $source->id,
            'payout_type' => $payoutType,
        ];

        $stripeTransferId = null;
        if ($seller) {
            $transfer = app(StripeConnectService::class)->createTransfer(
                $seller,
                $amount,
                $source->currency ?? 'usd',
                $transferMeta
            );
            if ($transfer['success'] ?? false) {
                $stripeTransferId = $transfer['transfer_id'] ?? null;
            }
        }

        Transaction::create([
            'user_id' => $sellerId,
            'seller_id' => $sellerId,
            'type' => 'payout',
            'amount' => $amount,
            'currency' => $source->currency ?? 'usd',
            'status' => 'completed',
            'payment_method' => $stripeTransferId ? 'stripe_connect' : 'platform',
            'reference_id' => $stripeTransferId ?: strtoupper($payoutType).'_'.$source->id,
            'metadata' => array_merge($transferMeta, array_filter([
                'stripe_transfer_id' => $stripeTransferId,
            ])),
            'completed_at' => now(),
        ]);
    }

    private function calculateEquityPercentage(float $amount, Business $business): float
    {
        $basis = (float) ($business->valuation ?: $business->funding_goal);
        if ($basis <= 0) {
            return 0;
        }

        return round(($amount / $basis) * 100, 4);
    }

    private function parsePaymentMetadata($metadata): array
    {
        if (is_object($metadata) && isset($metadata->metadata)) {
            $stripeMeta = $metadata->metadata;

            return is_array($stripeMeta) ? $stripeMeta : (array) $stripeMeta;
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_object($metadata)) {
            return (array) $metadata;
        }

        return [];
    }

    /**
     * Get user's wallet balance
     */
    public function getWalletBalance(User $user): float
    {
        $completedTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->get();

        $totalDeposits = $completedTransactions->where('type', 'deposit')->sum('amount');
        $totalPurchases = $completedTransactions->where('type', 'purchase')->sum('amount');
        $totalInvestments = $completedTransactions->where('type', 'investment')->sum('amount');
        $totalRefunds = $completedTransactions->where('type', 'refund')->sum('amount');
        $totalPayouts = $completedTransactions->where('type', 'payout')->sum('amount');
        $totalWithdrawals = $completedTransactions->where('type', 'withdrawal')->sum('amount');

        return $totalDeposits + $totalRefunds + $totalPayouts - $totalPurchases - $totalInvestments - $totalWithdrawals;
    }

    public function getAvailableWalletBalance(User $user): float
    {
        $balance = $this->getWalletBalance($user);
        $reserved = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        return max(0, round($balance - (float) $reserved, 2));
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(User $user, int $limit = 20): array
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return [
            'success' => true,
            'data' => $transactions,
            'total' => $transactions->count()
        ];
    }

    /**
     * Create Flutterwave payment for product purchase
     */
    public function createFlutterwaveProductPayment(User $user, Product $product, int $quantity = 1): array
    {
        try {
            $fwConfig = $this->gateways->getGateway('flutterwave');
            $secret = $this->gateways->getSecretKey('flutterwave');
            if (!$this->gateways->isEnabled('flutterwave') || !$secret) {
                return ['success' => false, 'error' => 'Flutterwave is not configured'];
            }

            $amount = $product->price * $quantity;
            $currency = $this->gateways->getPrimaryCurrency('flutterwave');
            $reference = 'BIZ_FW_' . time() . '_' . $user->id;

            $payload = [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'redirect_url' => $this->gateways->getCallbackUrl('flutterwave', $reference),
                'customer' => [
                    'email' => $user->email,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'product_purchase',
                ],
            ];

            $response = $this->flutterwaveRequest($secret, 'POST', 'https://api.flutterwave.com/v3/payments', $payload);
            $result = json_decode($response, true);

            if (($result['status'] ?? '') === 'success') {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => $amount,
                    'currency' => strtolower($currency),
                    'status' => 'pending',
                    'payment_method' => 'flutterwave',
                    'reference_id' => $reference,
                    'metadata' => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'flutterwave_reference' => $reference,
                        'payment_link' => $result['data']['link'] ?? null,
                    ],
                ]);

                return [
                    'success' => true,
                    'payment_link' => $result['data']['link'],
                    'reference' => $reference,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Flutterwave payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave product payment failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create Flutterwave payment for business investment
     */
    public function createFlutterwaveInvestmentPayment(User $user, Business $business, float $amount): array
    {
        try {
            if ($amount > $business->funding_goal) {
                return ['success' => false, 'error' => 'Investment amount exceeds funding goal'];
            }

            $fwConfig = $this->gateways->getGateway('flutterwave');
            $secret = $this->gateways->getSecretKey('flutterwave');
            if (!$this->gateways->isEnabled('flutterwave') || !$secret) {
                return ['success' => false, 'error' => 'Flutterwave is not configured'];
            }

            $currency = $this->gateways->getPrimaryCurrency('flutterwave');
            $reference = 'BIZ_FW_INV_' . time() . '_' . $user->id;

            $payload = [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'redirect_url' => $this->gateways->getCallbackUrl('flutterwave', $reference),
                'customer' => [
                    'email' => $user->email,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'type' => 'business_investment',
                ],
            ];

            $response = $this->flutterwaveRequest($secret, 'POST', 'https://api.flutterwave.com/v3/payments', $payload);
            $result = json_decode($response, true);

            if (($result['status'] ?? '') === 'success') {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'investment',
                    'amount' => $amount,
                    'currency' => strtolower($currency),
                    'status' => 'pending',
                    'payment_method' => 'flutterwave',
                    'reference_id' => $reference,
                    'metadata' => [
                        'business_id' => $business->id,
                        'flutterwave_reference' => $reference,
                        'payment_link' => $result['data']['link'] ?? null,
                    ],
                ]);

                return [
                    'success' => true,
                    'payment_link' => $result['data']['link'],
                    'reference' => $reference,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Flutterwave payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave investment payment failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify Flutterwave payment by transaction reference
     */
    public function verifyFlutterwavePayment(string $reference): array
    {
        try {
            $fwConfig = $this->gateways->getGateway('flutterwave');
            $secret = $this->gateways->getSecretKey('flutterwave');
            if (!$this->gateways->isEnabled('flutterwave') || !$secret) {
                return ['success' => false, 'error' => 'Flutterwave is not configured'];
            }

            $url = 'https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . urlencode($reference);
            $response = $this->flutterwaveRequest($secret, 'GET', $url);
            $result = json_decode($response, true);

            if (($result['status'] ?? '') === 'success' && ($result['data']['status'] ?? '') === 'successful') {
                $transaction = Transaction::where('reference_id', $reference)->first();

                if (!$transaction) {
                    return ['success' => false, 'error' => 'Transaction not found'];
                }

                $transaction->update([
                    'status' => 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'flutterwave_transaction_id' => $result['data']['id'],
                        'confirmed_at' => now()->toISOString(),
                    ]),
                ]);

                $meta = $result['data']['meta'] ?? [];
                $this->handlePostPayment($transaction, (object) $meta);

                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                ];
            }

            return ['success' => false, 'error' => 'Payment verification failed'];
        } catch (\Exception $e) {
            Log::error('Flutterwave verification failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate platform commission
     */
    public function calculateCommission(float $amount, string $type = 'sale', string $gateway = 'stripe'): float
    {
        $gatewayRate = $this->gateways->getCommissionRate($gateway, $type);
        $platform = $this->platformSettings->getPlatformSettings();
        $platformRate = ($type === 'investment'
            ? ($platform['investment_fee_rate'] ?? 3.0)
            : ($platform['commission_rate'] ?? 5.0)) / 100;

        $rate = max($gatewayRate, $platformRate);

        return round($amount * $rate, 2);
    }

    /**
     * Create a transaction with commission and net amount fields.
     */
    private function createPaymentTransaction(
        array $base,
        float $amount,
        string $gateway,
        string $commissionType,
        ?int $buyerId = null,
        ?int $sellerId = null,
        bool $platformRevenue = false
    ): Transaction {
        $commission = $platformRevenue ? 0 : $this->calculateCommission($amount, $commissionType, $gateway);
        $netAmount = $platformRevenue ? $amount : round($amount - $commission, 2);

        return Transaction::create(array_merge($base, [
            'amount' => $amount,
            'buyer_id' => $buyerId ?? ($base['user_id'] ?? null),
            'seller_id' => $sellerId,
            'commission' => $commission,
            'net_amount' => $netAmount,
        ]));
    }

    /**
     * Resolve subscription plan price for a currency.
     */
    public function resolvePlanPrice(string $planId, string $currency = 'USD'): ?array
    {
        $plans = $this->platformSettings->getPricingPlans();
        $plan = collect($plans)->firstWhere('id', $planId);

        if (!$plan || ($plan['is_free'] ?? false)) {
            return null;
        }

        $amount = (float) ($plan['prices'][strtoupper($currency)]['amount']
            ?? $plan['prices']['USD']['amount']
            ?? 0);

        if ($amount <= 0) {
            return null;
        }

        return ['plan' => $plan, 'amount' => $amount, 'currency' => strtoupper($currency)];
    }

    /**
     * Create Stripe payment intent for subscription plan purchase.
     */
    public function createSubscriptionPaymentIntent(User $user, string $planId, string $currency = 'USD'): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->ensureStripeConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not enabled or configured'];
        }

        $resolved = $this->resolvePlanPrice($planId, $currency);
        if (!$resolved) {
            return ['success' => false, 'error' => 'Invalid or free plan'];
        }

        try {
            $amount = $resolved['amount'];
            $amountInCents = (int) round($amount * 100);
            $customer = $this->getOrCreateCustomer($user);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => strtolower($currency) === 'ngn' ? 'ngn' : 'usd',
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $planId,
                    'type' => 'subscription',
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $transaction = $this->createPaymentTransaction([
                'user_id' => $user->id,
                'type' => 'subscription',
                'currency' => strtolower($currency),
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'metadata' => [
                    'plan_id' => $planId,
                    'type' => 'subscription',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ],
            ], $amount, 'stripe', 'sale', $user->id, null, true);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'plan_id' => $planId,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Subscription payment intent failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create Stripe payment intent for featured listing promotion budget.
     */
    public function createFeaturedListingPaymentIntent(User $user, float $amount, array $listingMeta = []): array
    {
        if (!$this->gateways->isEnabled('stripe') || !$this->ensureStripeConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not enabled or configured'];
        }

        $settings = $this->platformSettings->getPlatformSettings();
        $minDaily = (float) ($settings['featured_listing_min_daily_budget'] ?? 5);

        if ($amount < $minDaily) {
            return ['success' => false, 'error' => "Minimum promotion budget is {$minDaily}"];
        }

        try {
            $amountInCents = (int) round($amount * 100);
            $customer = $this->getOrCreateCustomer($user);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'customer' => $customer->id,
                'metadata' => array_merge([
                    'user_id' => $user->id,
                    'type' => 'featured_listing',
                ], $listingMeta),
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $transaction = $this->createPaymentTransaction([
                'user_id' => $user->id,
                'type' => 'featured_listing',
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'metadata' => array_merge([
                    'type' => 'featured_listing',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ], $listingMeta),
            ], $amount, 'stripe', 'sale', $user->id, null, true);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Featured listing payment intent failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Activate user subscription after successful payment.
     */
    public function activateSubscription(User $user, string $planId): void
    {
        $expiresAt = now()->addMonth();
        if ($user->subscription_expires_at && $user->subscription_expires_at->isFuture()) {
            $expiresAt = $user->subscription_expires_at->addMonth();
        }

        $user->update([
            'subscription_plan' => $planId,
            'subscription_expires_at' => $expiresAt,
        ]);
    }

    private function handleSubscriptionPayment(Transaction $transaction, $metadata): void
    {
        $meta = $this->parsePaymentMetadata($metadata);
        $planId = $meta['plan_id'] ?? ($transaction->metadata['plan_id'] ?? null);
        if (! $planId) {
            return;
        }

        $user = User::find($transaction->user_id);
        if (! $user) {
            return;
        }

        $this->activateSubscription($user, (string) $planId);
        $user->refresh();

        $planName = $this->resolvePlanDisplayName((string) $planId);
        $expires = $user->subscription_expires_at?->format('M j, Y') ?? 'N/A';
        $currency = strtoupper((string) ($transaction->currency ?: 'USD'));

        app(NotificationService::class)->notifyWithEmail(
            $user,
            'subscription_activated',
            'Subscription Activated',
            "Your {$planName} plan is now active until {$expires}.",
            [
                'plan_id' => $planId,
                'transaction_id' => $transaction->id,
                'action_url' => '/dashboard/settings',
                'action_label' => 'Manage Subscription',
                'email_details' => [
                    'Plan' => $planName,
                    'Expires' => $expires,
                    'Amount' => $currency.' '.number_format((float) $transaction->amount, 2),
                ],
            ],
            'high',
            "Subscription activated: {$planName}"
        );
    }

    private function handleFeaturedListingPayment(Transaction $transaction, $metadata): void
    {
        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], [
                'payment_confirmed' => true,
                'confirmed_at' => now()->toISOString(),
            ]),
        ]);

        $user = User::find($transaction->user_id);
        if (! $user) {
            return;
        }

        $currency = strtoupper((string) ($transaction->currency ?: 'USD'));

        app(NotificationService::class)->notifyWithEmail(
            $user,
            'transaction',
            'Promotion Payment Confirmed',
            'Your featured listing payment was successful. Complete your promotion setup to go live.',
            [
                'transaction_id' => $transaction->id,
                'action_url' => '/dashboard/featured-listings/create',
                'action_label' => 'Create Promotion',
                'email_details' => [
                    'Amount' => $currency.' '.number_format((float) $transaction->amount, 2),
                ],
            ],
            'medium',
            'Featured listing payment confirmed'
        );
    }

    private function resolvePlanDisplayName(string $planId): string
    {
        $resolved = $this->resolvePlanPrice($planId);
        if ($resolved && isset($resolved['plan']['name'])) {
            $name = $resolved['plan']['name'];
            if (is_array($name)) {
                return (string) ($name['en'] ?? $planId);
            }

            return (string) $name;
        }

        return ucfirst(str_replace('_', ' ', $planId));
    }

    private function flutterwaveRequest(string $secretKey, string $method, string $url, ?array $payload = null): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
} 