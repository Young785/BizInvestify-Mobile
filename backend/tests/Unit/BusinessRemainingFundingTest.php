<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRemainingFundingTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_funding_uses_funded_amount(): void
    {
        $business = Business::factory()->create([
            'funding_goal' => 100000,
            'funded_amount' => 40000,
        ]);

        $this->assertEquals(60000, $business->remainingFunding());
    }

    public function test_investment_validation_rejects_amount_above_remaining(): void
    {
        $business = Business::factory()->create([
            'funding_goal' => 50000,
            'funded_amount' => 45000,
            'valuation' => 500000,
        ]);

        $service = app(PaymentService::class);
        $method = new \ReflectionMethod(PaymentService::class, 'validateInvestmentAmount');
        $method->setAccessible(true);
        $result = $method->invoke($service, $business, 10000);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('remaining funding goal', $result['error']);
    }
}
