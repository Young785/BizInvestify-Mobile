<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_products_index_works(): void
    {
        Product::factory()->count(3)->create(['status' => 'active']);
        $this->getJson('/api/public/products')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_authenticated_products_index_requires_token(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_create_product_as_seller(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $seller = User::factory()->create(['role' => 'seller']);
        $seller->assignRole('seller');
        $this->actingAs($seller, 'sanctum');

        $payload = [
            'title' => 'Test Product',
            'description' => 'A description',
            'price' => 123.45,
            'category' => 'Tech',
            'inventory_count' => 10,
        ];

        $this->postJson('/api/products', $payload)
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.title', 'Test Product');
    }

    public function test_non_seller_cannot_create_product(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $buyer = User::factory()->create(['role' => 'buyer']);
        $buyer->assignRole('buyer');
        $this->actingAs($buyer, 'sanctum');

        $payload = [
            'title' => 'Test Product',
            'description' => 'A description',
            'price' => 123.45,
            'category' => 'Tech',
            'inventory_count' => 10,
        ];

        $this->postJson('/api/products', $payload)
            ->assertStatus(403);
    }
}


