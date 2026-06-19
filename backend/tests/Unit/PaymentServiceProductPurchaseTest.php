<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentServiceProductPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_purchase_completion_notifies_buyer_and_seller(): void
    {
        Event::fake([MessageSending::class]);

        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'inventory_count' => 10,
        ]);

        $transaction = Transaction::create([
            'user_id' => $buyer->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'listing_id' => $product->id,
            'listing_type' => 'product',
            'type' => 'purchase',
            'amount' => 50,
            'net_amount' => 47.5,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
            'reference_id' => 'pi_test_product',
            'metadata' => [
                'product_id' => $product->id,
                'quantity' => 2,
                'type' => 'product_purchase',
            ],
        ]);

        $service = app(PaymentService::class);
        $method = new \ReflectionMethod(PaymentService::class, 'handleProductPurchase');
        $method->setAccessible(true);
        $method->invoke($service, $transaction, (object) [
            'product_id' => (string) $product->id,
            'quantity' => '2',
            'type' => 'product_purchase',
        ]);

        $product->refresh();
        $this->assertEquals(8, $product->inventory_count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => 'transaction',
            'title' => 'Purchase Confirmed',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $seller->id,
            'type' => 'payment_received',
            'title' => 'New Sale Received',
        ]);

        Event::assertDispatched(MessageSending::class, 2);
    }
}
