<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class MarketplaceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoints_work(): void
    {
        $this->getJson('/api/public/categories')->assertStatus(200);
        $this->getJson('/api/public/featured')->assertStatus(200);
        $this->getJson('/api/public/trending')->assertStatus(200);
        $this->getJson('/api/public/marketplace/stats')->assertStatus(200);
        $this->getJson('/api/public/marketplace/recommendations?type=product')
            ->assertStatus(200);
    }

    public function test_authenticated_stats_requires_token(): void
    {
        $this->getJson('/api/marketplace/stats')->assertStatus(401);
    }

    public function test_authenticated_marketplace_stats_returns_data(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/marketplace/stats')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}


