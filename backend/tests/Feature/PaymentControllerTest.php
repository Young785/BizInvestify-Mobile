<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_payment_methods_returns_empty_array(): void
    {
        // actingAs to ensure session and auth context
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->withoutMiddleware();
        $this->getJson('/api/payments/methods')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertExactJson(['success' => true, 'data' => []]);
    }

    public function test_process_bank_transfer_validates_and_creates_records(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($user, 'sanctum');

        $business = Business::factory()->create(['valuation' => 100000]);

        $payload = [
            'payment_intent' => 'INT-1',
            'amount' => 1000,
            'business_id' => $business->id,
            'transfer_type' => 'bank_transfer',
        ];

        $this->postJson('/api/payments/bank-transfer', $payload)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['transaction_id', 'investment_id']);
    }
}


