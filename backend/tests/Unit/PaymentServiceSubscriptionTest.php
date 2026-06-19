<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentServiceSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_payment_activates_plan_and_notifies_user(): void
    {
        Event::fake([MessageSending::class]);
        $this->seed(PlatformSettingsSeeder::class);

        $user = User::factory()->create(['role' => 'seller']);
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'subscription',
            'amount' => 29,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
            'reference_id' => 'pi_test_sub',
            'metadata' => [
                'type' => 'subscription',
                'plan_id' => 'professional',
            ],
        ]);

        $service = app(PaymentService::class);
        $method = new \ReflectionMethod(PaymentService::class, 'handleSubscriptionPayment');
        $method->setAccessible(true);
        $method->invoke($service, $transaction, (object) [
            'type' => 'subscription',
            'plan_id' => 'professional',
        ]);

        $user->refresh();
        $this->assertSame('professional', $user->subscription_plan);
        $this->assertNotNull($user->subscription_expires_at);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'subscription_activated',
            'title' => 'Subscription Activated',
        ]);

        Event::assertDispatched(MessageSending::class);
    }
}
