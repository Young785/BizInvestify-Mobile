<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderFulfillmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipped_status_notifies_buyer(): void
    {
        Event::fake([MessageSending::class]);

        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'amount' => 100,
            'currency' => 'USD',
            'status' => 'confirmed',
        ]);

        $service = app(OrderFulfillmentService::class);
        $service->updateStatus($order, 'processing', $seller);
        $service->updateStatus($order->fresh(), 'shipped', $seller, 'Left warehouse');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => 'order_update',
            'title' => 'Order Shipped',
        ]);

        Event::assertDispatched(MessageSending::class);
    }
}
