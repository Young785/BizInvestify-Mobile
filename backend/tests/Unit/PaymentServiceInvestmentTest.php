<?php

namespace Tests\Unit;

use App\Models\BankAccount;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentServiceInvestmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_investment_completion_creates_investment_and_updates_funding(): void
    {
        Event::fake([MessageSending::class]);

        $seller = User::factory()->create(['role' => 'seller']);
        $investor = User::factory()->create(['role' => 'buyer']);
        $business = Business::factory()->create([
            'seller_id' => $seller->id,
            'funding_goal' => 100000,
            'funded_amount' => 0,
            'valuation' => 100000,
        ]);

        $transaction = Transaction::create([
            'user_id' => $investor->id,
            'buyer_id' => $investor->id,
            'seller_id' => $seller->id,
            'listing_id' => $business->id,
            'listing_type' => 'business',
            'type' => 'investment',
            'amount' => 10000,
            'net_amount' => 9500,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
            'reference_id' => 'pi_test_123',
            'metadata' => [
                'business_id' => $business->id,
                'type' => 'business_investment',
            ],
        ]);

        $service = app(PaymentService::class);
        $method = new \ReflectionMethod(PaymentService::class, 'handleBusinessInvestment');
        $method->setAccessible(true);
        $method->invoke($service, $transaction, (object) [
            'metadata' => [
                'business_id' => (string) $business->id,
                'type' => 'business_investment',
            ],
        ]);

        $this->assertDatabaseHas('investments', [
            'investor_id' => $investor->id,
            'business_id' => $business->id,
            'status' => 'completed',
            'amount' => 10000,
        ]);

        $business->refresh();
        $this->assertEquals(10000, (float) $business->funded_amount);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $seller->id,
            'type' => 'payout',
            'amount' => 9500,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $investor->id,
            'type' => 'investment_received',
        ]);

        Event::assertDispatched(MessageSending::class, 2);
    }

    public function test_available_wallet_balance_reserves_pending_withdrawals(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'payout',
            'amount' => 500,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'platform',
            'completed_at' => now(),
        ]);

        $bankAccount = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Test Bank',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'account_number_last4' => '78901234',
            'routing_number' => '021000021',
            'account_type' => 'checking',
            'currency' => 'USD',
            'status' => 'pending',
            'is_default' => true,
        ]);

        \App\Models\Withdrawal::create([
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'amount' => 200,
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => 'WD-TEST123',
        ]);

        $service = app(PaymentService::class);
        $this->assertEquals(300.0, $service->getAvailableWalletBalance($user));
    }
}
