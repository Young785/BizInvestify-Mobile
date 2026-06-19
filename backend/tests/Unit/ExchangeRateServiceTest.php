<?php

namespace Tests\Unit;

use App\Exceptions\ExchangeRateUnavailableException;
use App\Services\ExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fallback_supports_usd_cross_rates(): void
    {
        $service = app(ExchangeRateService::class);

        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $this->assertEqualsWithDelta(0.92, $service->getCrossRate('USD', 'EUR'), 0.001);
        $this->assertEqualsWithDelta(1.0, $service->getCrossRate('EUR', 'EUR'), 0.001);
    }

    public function test_unknown_currency_pair_throws(): void
    {
        $service = app(ExchangeRateService::class);

        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $this->expectException(ExchangeRateUnavailableException::class);
        $service->getCrossRate('USD', 'XYZ');
    }
}
