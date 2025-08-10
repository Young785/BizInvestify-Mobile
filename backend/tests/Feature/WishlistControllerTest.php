<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_and_remove_wishlist_item(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $seller = User::factory()->create(['role' => 'seller']);
        $seller->assignRole('seller');
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $buyer = User::factory()->create(['role' => 'buyer']);
        $buyer->assignRole('buyer');
        $this->actingAs($buyer, 'sanctum');

        $add = $this->postJson('/api/wishlist', [
                'item_type' => 'product',
                'item_id' => $product->id,
                'notes' => 'Looks good',
            ]);
        $add->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wishlistable.id', $product->id);

        $id = $add->json('data.id');

        $this->deleteJson('/api/wishlist/'.$id)
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}


