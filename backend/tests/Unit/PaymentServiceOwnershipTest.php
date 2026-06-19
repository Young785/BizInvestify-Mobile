<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_owns_transaction_when_buyer_or_initiator(): void
    {
        $buyer = User::factory()->create();
        $other = User::factory()->create();

        $transaction = Transaction::create([
            'user_id' => $buyer->id,
            'buyer_id' => $buyer->id,
            'type' => 'purchase',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'stripe',
            'reference_id' => 'pi_owner_test',
        ]);

        $service = app(PaymentService::class);

        $this->assertTrue($service->userOwnsTransaction($transaction, $buyer));
        $this->assertFalse($service->userOwnsTransaction($transaction, $other));
    }

    public function test_verify_paystack_rejects_unauthorized_user(): void
    {
        $buyer = User::factory()->create();
        $intruder = User::factory()->create();

        Transaction::create([
            'user_id' => $buyer->id,
            'buyer_id' => $buyer->id,
            'type' => 'purchase',
            'amount' => 40,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'paystack',
            'reference_id' => 'BIZ_unauthorized_ref',
        ]);

        $service = app(PaymentService::class);
        $result = $service->verifyPaystackPayment('BIZ_unauthorized_ref', $intruder);

        $this->assertFalse($result['success']);
        $this->assertSame('Unauthorized', $result['error']);
        $this->assertSame(403, $result['status']);
    }
}
