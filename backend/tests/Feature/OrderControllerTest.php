<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_view_own_order(): void
    {
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
            'status' => 'pending',
        ]);

        $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/orders/'.$order->id)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_buyer_orders_appear_in_index(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'amount' => 50,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/orders')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 1);
    }
}
