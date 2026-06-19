<?php

namespace App\Services;

use App\Exceptions\ExchangeRateUnavailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const BASE_URL = 'https://wise.com/gateway/v4/comparisons';

    private const CACHE_TTL_SECONDS = 3600;

  /**
     * Mid-market rate from Wise (source → target).
     */
    public function getRate(string $sourceCurrency, string $targetCurrency, string $sourceCountry = 'US'): float
    {
        $sourceCurrency = strtoupper($sourceCurrency);
        $targetCurrency = strtoupper($targetCurrency);
        $sourceCountry = strtoupper($sourceCountry);

        if ($sourceCurrency === $targetCurrency) {
            return 1.0;
        }

        $cacheKey = "wise_rate:{$sourceCurrency}:{$targetCurrency}:{$sourceCountry}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($sourceCurrency, $targetCurrency, $sourceCountry) {
            return $this->fetchWiseRate($sourceCurrency, $targetCurrency, $sourceCountry);
        });
    }

    public function convert(float $amount, string $sourceCurrency, string $targetCurrency, string $sourceCountry = 'US'): float
    {
        return $this->convertBetween($amount, $sourceCurrency, $targetCurrency, $sourceCountry);
    }

    public function convertBetween(float $amount, string $fromCurrency, string $toCurrency, string $sourceCountry = 'US'): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);
        $sourceCountry = strtoupper($sourceCountry);

        if ($fromCurrency === $toCurrency) {
            return $this->roundForCurrency($amount, $toCurrency);
        }

        $usdAmount = $fromCurrency === 'USD'
            ? $amount
            : ($amount / $this->getRate('USD', $fromCurrency, $sourceCountry));

        if ($toCurrency === 'USD') {
            return $this->roundForCurrency($usdAmount, 'USD');
        }

        $converted = $usdAmount * $this->getRate('USD', $toCurrency, $sourceCountry);

        return $this->roundForCurrency($converted, $toCurrency);
    }

    public function getCrossRate(string $fromCurrency, string $toCurrency, string $sourceCountry = 'US'): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        return $this->convertBetween(1, $fromCurrency, $toCurrency, $sourceCountry);
    }

    private function roundForCurrency(float $amount, string $currency): float
    {
        return $currency === 'JPY'
            ? round($amount, 0)
            : round($amount, 2);
    }

    private function fetchWiseRate(string $sourceCurrency, string $targetCurrency, string $sourceCountry): float
    {
        try {
            $response = Http::timeout(8)->get(self::BASE_URL, [
                'sourceCurrency' => $sourceCurrency,
                'targetCurrency' => $targetCurrency,
                'sendAmount' => 1000,
                'sourceCountry' => $sourceCountry,
                'filter' => 'POPULAR',
                'includeWise' => true,
                'numberOfProviders' => 3,
            ]);

            if (!$response->successful()) {
                Log::warning('Wise rate request failed', [
                    'status' => $response->status(),
                    'pair' => "{$sourceCurrency}/{$targetCurrency}",
                ]);

                return $this->fallbackRate($sourceCurrency, $targetCurrency);
            }

            $data = $response->json();
            $providers = $data['providers'] ?? [];

            foreach ($providers as $provider) {
                if (($provider['alias'] ?? '') !== 'wise') {
                    continue;
                }

                foreach ($provider['quotes'] ?? [] as $quote) {
                    if (!empty($quote['isConsideredMidMarketRate']) && isset($quote['rate'])) {
                        return (float) $quote['rate'];
                    }
                }

                $firstQuote = $provider['quotes'][0] ?? null;
                if ($firstQuote && isset($firstQuote['rate'])) {
                    return (float) $firstQuote['rate'];
                }
            }

            Log::warning('Wise mid-market rate not found', ['pair' => "{$sourceCurrency}/{$targetCurrency}"]);

            return $this->fallbackRate($sourceCurrency, $targetCurrency);
        } catch (\Exception $e) {
            Log::error('Wise rate fetch error: ' . $e->getMessage());

            return $this->fallbackRate($sourceCurrency, $targetCurrency);
        }
    }

    /** Static fallbacks when Wise is unavailable (USD pivot). */
    private function fallbackRate(string $sourceCurrency, string $targetCurrency): float
    {
        if ($sourceCurrency === $targetCurrency) {
            return 1.0;
        }

        $table = [
            'EUR' => 0.92,
            'GBP' => 0.79,
            'NGN' => 1373.0,
            'INR' => 83.0,
            'AED' => 3.67,
            'ZAR' => 18.0,
            'BRL' => 5.0,
            'CAD' => 1.36,
            'JPY' => 157.0,
        ];

        if ($sourceCurrency === 'USD' && isset($table[$targetCurrency])) {
            return $table[$targetCurrency];
        }

        if ($targetCurrency === 'USD' && isset($table[$sourceCurrency])) {
            return 1 / $table[$sourceCurrency];
        }

        if (isset($table[$sourceCurrency], $table[$targetCurrency])) {
            return $table[$targetCurrency] / $table[$sourceCurrency];
        }

        throw new ExchangeRateUnavailableException(
            "Exchange rate unavailable for {$sourceCurrency}/{$targetCurrency}"
        );
    }
}
