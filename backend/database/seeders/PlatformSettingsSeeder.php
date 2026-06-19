<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\PaymentGatewayService;
use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PlatformSettingsService::class);

        Setting::updateOrCreate(
            ['key' => PlatformSettingsService::KEY_PLATFORM],
            [
                'value' => $service->defaultPlatformSettings(),
                'type' => Setting::TYPE_JSON,
                'group' => 'platform',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => PlatformSettingsService::KEY_PRICING],
            [
                'value' => $service->defaultPricingPlans(),
                'type' => Setting::TYPE_JSON,
                'group' => 'pricing',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => PlatformSettingsService::KEY_LOCALIZATION],
            [
                'value' => $service->defaultLocalizationConfig(),
                'type' => Setting::TYPE_JSON,
                'group' => 'localization',
                'is_public' => true,
            ]
        );

        $gatewayService = app(PaymentGatewayService::class);
        Setting::updateOrCreate(
            ['key' => PaymentGatewayService::KEY],
            [
                'value' => $gatewayService->defaultConfig(),
                'type' => Setting::TYPE_JSON,
                'group' => 'payments',
                'is_public' => false,
            ]
        );
    }
}
