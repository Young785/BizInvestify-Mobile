<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for product purchase
     */
    public function createProductPaymentIntent(User $user, Product $product, int $quantity = 1): array
    {
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
                    'type' => 'product_purchase'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $amount / 100,
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'metadata' => [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'stripe_payment_intent_id' => $paymentIntent->id
                ]
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount / 100
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
                    'type' => 'business_investment'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'investment',
                'amount' => $amount,
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'stripe',
                'reference_id' => $paymentIntent->id,
                'metadata' => [
                    'business_id' => $business->id,
                    'stripe_payment_intent_id' => $paymentIntent->id
                ]
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount
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
            $headers = [
                "Authorization: Bearer " . config('services.paystack.secret'),
                "Cache-Control: no-cache",
            ];

            $fields = [
                'email' => $user->email,
                'amount' => $amount,
                'currency' => 'NGN',
                'reference' => 'BIZ_' . time() . '_' . $user->id,
                'callback_url' => config('app.url') . '/api/payments/paystack/callback',
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
                // Create transaction record
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => $amount / 100,
                    'currency' => 'ngn',
                    'status' => 'pending',
                    'payment_method' => 'paystack',
                    'reference_id' => $result['data']['reference'],
                    'metadata' => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'paystack_reference' => $result['data']['reference'],
                        'authorization_url' => $result['data']['authorization_url']
                    ]
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                    'transaction_id' => $transaction->id,
                    'amount' => $amount / 100
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
            $headers = [
                "Authorization: Bearer " . config('services.paystack.secret'),
                "Cache-Control: no-cache",
            ];

            $fields = [
                'email' => $user->email,
                'amount' => $amountInKobo,
                'currency' => 'NGN',
                'reference' => 'BIZ_INV_' . time() . '_' . $user->id,
                'callback_url' => config('app.url') . '/api/payments/paystack/callback',
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
            $headers = [
                "Authorization: Bearer " . config('services.paystack.secret'),
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

            if ($result['status'] && $result['data']['status'] === 'success') {
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
            // Create escrow transaction
            $transaction = Transaction::create([
                'user_id' => $buyer->id,
                'type' => 'escrow',
                'amount' => $amount,
                'currency' => 'usd',
                'status' => 'pending',
                'payment_method' => 'escrow',
                'reference_id' => 'ESCROW_' . time() . '_' . $buyer->id,
                'metadata' => [
                    'business_id' => $business->id,
                    'seller_id' => $business->user_id,
                    'escrow_type' => 'business_acquisition',
                    'release_conditions' => [
                        'due_diligence_completed' => false,
                        'legal_documents_signed' => false,
                        'transfer_completed' => false
                    ]
                ]
            ]);

            return [
                'success' => true,
                'escrow_id' => $transaction->reference_id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'status' => 'pending'
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
            $transaction = Transaction::where('reference_id', $escrowId)
                ->where('type', 'escrow')
                ->where('status', 'pending')
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'error' => 'Escrow transaction not found'
                ];
            }

            // Check if all conditions are met
            $metadata = $transaction->metadata;
            $releaseConditions = $metadata['release_conditions'] ?? [];

            foreach ($conditions as $condition => $value) {
                if (isset($releaseConditions[$condition]) && !$value) {
                    return [
                        'success' => false,
                        'error' => "Condition not met: {$condition}"
                    ];
                }
            }

            // Update transaction status
            $transaction->update([
                'status' => 'completed',
                'metadata' => array_merge($metadata, [
                    'released_at' => now()->toISOString(),
                    'release_conditions' => $conditions
                ])
            ]);

            // Transfer funds to seller
            $sellerId = $metadata['seller_id'];
            $this->transferFundsToSeller($sellerId, $transaction->amount, $transaction->id);

            return [
                'success' => true,
                'escrow_id' => $escrowId,
                'amount' => $transaction->amount,
                'released_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Escrow funds release failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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
                    'status' => 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'stripe_charge_id' => $paymentIntent->latest_charge,
                        'confirmed_at' => now()->toISOString()
                    ])
                ]);

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
                $headers = [
                    "Authorization: Bearer " . config('services.paystack.secret'),
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
        try {
            $account = Account::create([
                'type' => 'express',
                'country' => 'US', // Default, should be configurable
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'metadata' => [
                    'user_id' => $user->id
                ]
            ]);

            $user->update(['stripe_account_id' => $account->id]);

            return [
                'success' => true,
                'account_id' => $account->id,
                'onboarding_url' => $account->onboarding_url
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect account creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Connect account status
     */
    public function getConnectAccountStatus(User $user): array
    {
        try {
            if (!$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'No Stripe account found'
                ];
            }

            $account = Account::retrieve($user->stripe_account_id);

            return [
                'success' => true,
                'account_id' => $account->id,
                'status' => $account->charges_enabled ? 'active' : 'pending',
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'requirements' => $account->requirements
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect account status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle post-payment logic
     */
    private function handlePostPayment(Transaction $transaction, $metadata): void
    {
        $type = is_object($metadata) ? $metadata->type : $metadata['type'];

        if ($type === 'product_purchase') {
            // Handle product purchase
            $this->handleProductPurchase($transaction, $metadata);
        } elseif ($type === 'business_investment') {
            // Handle business investment
            $this->handleBusinessInvestment($transaction, $metadata);
        }
    }

    /**
     * Handle product purchase completion
     */
    private function handleProductPurchase(Transaction $transaction, $metadata): void
    {
        $productId = is_object($metadata) ? $metadata->product_id : $metadata['product_id'];
        $quantity = is_object($metadata) ? $metadata->quantity : $metadata['quantity'];

        // Update product inventory
        $product = Product::find($productId);
        if ($product) {
            // Update inventory if applicable
            if ($product->inventory_tracking) {
                $product->decrement('stock_quantity', $quantity);
            }
        }

        // Create order record
        // Send confirmation emails
        // Update seller balance
        // etc.
    }

    /**
     * Handle business investment completion
     */
    private function handleBusinessInvestment(Transaction $transaction, $metadata): void
    {
        $businessId = is_object($metadata) ? $metadata->business_id : $metadata['business_id'];

        // Update business funding progress
        $business = Business::find($businessId);
        if ($business) {
            $business->increment('funded_amount', $transaction->amount);
            
            // Check if funding goal is reached
            if ($business->funded_amount >= $business->funding_goal) {
                $business->update(['status' => 'funded']);
            }
        }

        // Create investment record
        // Send confirmation emails
        // Update investor portfolio
        // etc.
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

        return $totalDeposits + $totalRefunds + $totalPayouts - $totalPurchases - $totalInvestments;
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
     * Calculate platform commission
     */
    public function calculateCommission(float $amount, string $type = 'sale'): float
    {
        $commissionRate = config('services.stripe.commission_rate', 0.05); // 5% default
        
        if ($type === 'investment') {
            $commissionRate = config('services.stripe.investment_fee_rate', 0.03); // 3% for investments
        }

        return $amount * $commissionRate;
    }
} 