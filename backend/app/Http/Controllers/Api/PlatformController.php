<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlatformController extends Controller
{
    public function __construct(
        private PlatformSettingsService $settings,
        private PaymentGatewayService $paymentGateways,
    ) {
    }

    /**
     * Public platform config: localization options + pricing for visitor locale.
     */
    public function getPublicConfig(Request $request): JsonResponse
    {
        try {
            $data = $this->settings->getPublicConfig(
                $request->query('country'),
                $request->query('currency'),
                $request->query('language')
            );
            $data['payment_gateways'] = $this->paymentGateways->getPublicGateways();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Public platform config error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load platform configuration',
            ], 500);
        }
    }

    /**
     * Public pricing plans only.
     */
    public function getPublicPricing(Request $request): JsonResponse
    {
        try {
            $config = $this->settings->getPublicConfig(
                $request->query('country'),
                $request->query('currency'),
                $request->query('language')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'country' => $config['country'],
                    'currency' => $config['currency'],
                    'language' => $config['language'],
                    'plans' => $config['pricing_plans'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Public pricing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load pricing',
            ], 500);
        }
    }

    /**
     * Public enabled payment gateways (public keys only).
     */
    public function getPublicPaymentGateways(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->paymentGateways->getPublicGateways(),
            ]);
        } catch (\Exception $e) {
            Log::error('Public payment gateways error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment gateways',
            ], 500);
        }
    }
}
