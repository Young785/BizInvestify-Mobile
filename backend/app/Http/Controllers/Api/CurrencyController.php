<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\ExchangeRateUnavailableException;
use App\Services\ExchangeRateService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRates,
        private PlatformSettingsService $platformSettings,
    ) {
    }

    /**
     * Get exchange rate between two currencies.
     */
    public function getRate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from' => 'required|string|size:3',
                'to' => 'required|string|size:3',
                'country' => 'nullable|string|size:2',
            ]);

            $from = strtoupper($validated['from']);
            $to = strtoupper($validated['to']);
            $country = strtoupper($validated['country'] ?? 'US');

            if ($from === $to) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'from' => $from,
                        'to' => $to,
                        'rate' => 1.0,
                        'provider' => 'identity',
                    ],
                ]);
            }

            $rate = $this->exchangeRates->getCrossRate($from, $to, $country);

            return response()->json([
                'success' => true,
                'data' => [
                    'from' => $from,
                    'to' => $to,
                    'rate' => $rate,
                    'provider' => 'wise',
                ],
            ]);
        } catch (ExchangeRateUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Currency rate lookup failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exchange rate',
            ], 500);
        }
    }

    /**
     * Convert an amount between currencies.
     */
    public function convert(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'from' => 'required|string|size:3',
                'to' => 'required|string|size:3',
                'country' => 'nullable|string|size:2',
            ]);

            $from = strtoupper($validated['from']);
            $to = strtoupper($validated['to']);
            $country = strtoupper($validated['country'] ?? 'US');
            $amount = (float) $validated['amount'];

            $converted = $this->exchangeRates->convertBetween($amount, $from, $to, $country);
            $rate = $this->exchangeRates->getCrossRate($from, $to, $country);

            return response()->json([
                'success' => true,
                'data' => [
                    'amount' => $amount,
                    'converted_amount' => $converted,
                    'from' => $from,
                    'to' => $to,
                    'rate' => $rate,
                    'provider' => 'wise',
                ],
            ]);
        } catch (ExchangeRateUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Currency conversion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to convert currency',
            ], 500);
        }
    }

    /**
     * List supported currencies from platform localization config.
     */
    public function getSupportedCurrencies(): JsonResponse
    {
        try {
            $localization = $this->platformSettings->getLocalizationConfig();

            return response()->json([
                'success' => true,
                'data' => [
                    'default_currency' => $localization['default_currency'] ?? 'USD',
                    'currencies' => $localization['currencies'] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Supported currencies lookup failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load currencies',
            ], 500);
        }
    }
}
