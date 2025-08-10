<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_products_endpoint_requires_auth(): void
    {
        $this->getJson('/api/search/products')->assertStatus(401);
    }

    public function test_search_products_authenticated(): void
    {
        $user = User::factory()->create();

        // Mock SearchService response to avoid heavy logic
        $this->mock(SearchService::class, function ($mock) {
            $mock->shouldReceive('searchProducts')->andReturn([
                'data' => [],
                'stats' => ['count' => 0],
                'filters' => [],
            ]);
        });

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/search/products?q=a')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}


