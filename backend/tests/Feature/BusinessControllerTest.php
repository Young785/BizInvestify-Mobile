<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_businesses_index_works(): void
    {
        Business::factory()->count(2)->create(['status' => 'active']);
        $this->getJson('/api/public/businesses')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_create_business_as_seller(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $seller = User::factory()->create(['role' => 'seller']);
        $seller->assignRole('seller');
        $token = $seller->createToken('t')->plainTextToken;

        $payload = [
            'name' => 'Biz 1',
            'description' => 'Desc',
            'industry' => 'Tech',
            'valuation' => 100000,
            'funding_goal' => 50000,
            'equity_offered' => 10,
            'location' => 'NYC',
        ];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/businesses', $payload)
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.business.name', 'Biz 1');
    }
}


