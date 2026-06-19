<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    public const KEY = 'payment_gateways';
    public const KEY_PAYMENT_GATEWAYS = self::KEY;

    private const SENSITIVE_FIELDS = [
        'secret_key',
        'webhook_secret',
        'encryption_key',
        'client_secret',
    ];

    public function getGateways(bool $maskSecrets = true): array
    {
        $stored = Setting::get(self::KEY);
        $config = $this->mergeWithDefaults($stored ?? []);

        if ($maskSecrets) {
            $config = $this->maskSensitiveValues($config);
        }

        return $config;
    }

    public function getConfigForAdmin(): array
    {
        return $this->getGateways(true);
    }

    public function saveConfig(array $input): array
    {
        return $this->saveGateways($input);
    }

    public function defaultConfig(): array
    {
        return $this->defaultGateways();
    }

    public function getPublicGateways(): array
    {
        return $this->getPublicPaymentMethods();
    }

    public function saveGateways(array $input): array
    {
        $existing = Setting::get(self::KEY);
        $current = $this->mergeWithDefaults($existing ?? []);
        $merged = $this->mergeGatewayUpdates($current, $input);

        Setting::updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => $merged,
                'type' => Setting::TYPE_JSON,
                'group' => 'payments',
                'is_public' => false,
            ]
        );

        return $this->maskSensitiveValues($merged);
    }

    public function getGateway(string $gateway): ?array
    {
        $config = $this->mergeWithDefaults(Setting::get(self::KEY) ?? []);
        $gatewayConfig = $config['gateways'][$gateway] ?? null;

        if (!$gatewayConfig) {
            return null;
        }

        return $this->resolveGatewayCredentials($gateway, $gatewayConfig);
    }

    public function isEnabled(string $gateway): bool
    {
        $config = $this->getGateway($gateway);

        if (!$config || !($config['enabled'] ?? false)) {
            return false;
        }

        if ($gateway === 'bank_transfer') {
            return !empty($config['account_number']) || !empty($config['instructions']);
        }

        return !empty($config['secret_key']);
    }

    public function getEnabledGateways(): array
    {
        $config = $this->mergeWithDefaults(Setting::get(self::KEY) ?? []);
        $enabled = [];

        foreach ($config['gateways'] as $id => $gateway) {
            if ($this->isEnabled($id)) {
                $enabled[$id] = $this->resolveGatewayCredentials($id, $gateway);
            }
        }

        return $enabled;
    }

    public function getPublicPaymentMethods(): array
    {
        $config = $this->mergeWithDefaults(Setting::get(self::KEY) ?? []);
        $methods = [];

        foreach ($config['gateways'] as $id => $gateway) {
            if (!($gateway['enabled'] ?? false)) {
                continue;
            }

            $resolved = $this->resolveGatewayCredentials($id, $gateway);
            if (empty($resolved['secret_key']) && $id !== 'bank_transfer') {
                continue;
            }

            $methods[] = [
                'id' => $id,
                'name' => $gateway['name'] ?? ucfirst(str_replace('_', ' ', $id)),
                'mode' => $gateway['mode'] ?? 'test',
                'public_key' => $resolved['public_key'] ?? null,
                'supported_currencies' => $gateway['supported_currencies'] ?? [],
                'is_default' => ($config['default_gateway'] ?? 'stripe') === $id,
            ];
        }

        return [
            'default_gateway' => $config['default_gateway'] ?? 'stripe',
            'methods' => $methods,
        ];
    }

    public function getCommissionRate(string $gateway, string $type = 'sale'): float
    {
        $config = $this->getGateway($gateway);
        if (!$config) {
            return $type === 'investment' ? 0.03 : 0.05;
        }

        $rate = $type === 'investment'
            ? ($config['investment_fee_rate'] ?? 3.0)
            : ($config['commission_rate'] ?? 5.0);

        return $rate / 100;
    }

    public function getWebhookSecret(string $gateway): ?string
    {
        $config = $this->getGateway($gateway);

        return $config['webhook_secret'] ?? null;
    }

    public function getSecretKey(string $gateway): ?string
    {
        $config = $this->getGateway($gateway);
        $secret = $config['secret_key'] ?? null;

        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    public function getPublicKey(string $gateway): ?string
    {
        $config = $this->getGateway($gateway);
        $public = $config['public_key'] ?? null;

        return is_string($public) && $public !== '' ? $public : null;
    }

    public function getPrimaryCurrency(string $gateway): string
    {
        $config = $this->getGateway($gateway) ?? [];

        return strtoupper($config['supported_currencies'][0] ?? 'USD');
    }

    public function getCallbackUrl(string $provider, string $reference = ''): string
    {
        $base = rtrim(config('app.url'), '/');
        $query = $reference ? ('?reference=' . urlencode($reference)) : '';

        return match ($provider) {
            'paystack' => "{$base}/api/payments/paystack/callback{$query}",
            'flutterwave' => "{$base}/api/payments/flutterwave/callback{$query}",
            default => "{$base}/api/payments/{$provider}/callback{$query}",
        };
    }

    public function getFrontendRedirectUrl(string $status, ?string $reference = null): string
    {
        $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
        $params = http_build_query(array_filter([
            'status' => $status,
            'reference' => $reference,
        ]));

        return "{$frontend}/payments/result?{$params}";
    }

    public function testConnection(string $gateway): array
    {
        $config = $this->getGateway($gateway);

        if (!$config) {
            return ['success' => false, 'message' => 'Unknown payment gateway'];
        }

        if (empty($config['secret_key']) && $gateway !== 'bank_transfer') {
            return ['success' => false, 'message' => 'Secret key is not configured'];
        }

        try {
            return match ($gateway) {
                'stripe' => $this->testStripe($config),
                'paystack' => $this->testPaystack($config),
                'flutterwave' => $this->testFlutterwave($config),
                'paypal' => $this->testPaypal($config),
                'razorpay' => $this->testRazorpay($config),
                'bank_transfer' => ['success' => true, 'message' => 'Bank transfer does not require API credentials'],
                default => ['success' => false, 'message' => 'Gateway test not implemented'],
            };
        } catch (\Exception $e) {
            Log::error("Payment gateway test failed [{$gateway}]: " . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function defaultGateways(): array
    {
        return [
            'default_gateway' => 'stripe',
            'gateways' => [
                'stripe' => [
                    'name' => 'Stripe',
                    'enabled' => !empty(config('services.stripe.secret')),
                    'mode' => str_starts_with((string) config('services.stripe.secret'), 'sk_live') ? 'live' : 'test',
                    'public_key' => config('services.stripe.key', ''),
                    'secret_key' => config('services.stripe.secret', ''),
                    'webhook_secret' => config('services.stripe.webhook_secret', ''),
                    'commission_rate' => (float) config('services.stripe.commission_rate', 0.05) * 100,
                    'investment_fee_rate' => (float) config('services.stripe.investment_fee_rate', 0.03) * 100,
                    'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD'],
                ],
                'paystack' => [
                    'name' => 'Paystack',
                    'enabled' => !empty(config('services.paystack.secret')),
                    'mode' => str_starts_with((string) config('services.paystack.secret'), 'sk_live') ? 'live' : 'test',
                    'public_key' => config('services.paystack.public', ''),
                    'secret_key' => config('services.paystack.secret', ''),
                    'webhook_secret' => config('services.paystack.webhook_secret', ''),
                    'commission_rate' => (float) config('services.paystack.commission_rate', 0.05) * 100,
                    'investment_fee_rate' => (float) config('services.paystack.investment_fee_rate', 0.03) * 100,
                    'supported_currencies' => ['NGN', 'GHS', 'ZAR', 'USD'],
                ],
                'flutterwave' => [
                    'name' => 'Flutterwave',
                    'enabled' => false,
                    'mode' => 'test',
                    'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', ''),
                    'secret_key' => env('FLUTTERWAVE_SECRET_KEY', ''),
                    'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY', ''),
                    'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET', ''),
                    'commission_rate' => (float) env('FLUTTERWAVE_COMMISSION_RATE', 0.05) * 100,
                    'investment_fee_rate' => (float) env('FLUTTERWAVE_INVESTMENT_FEE_RATE', 0.03) * 100,
                    'supported_currencies' => ['NGN', 'USD', 'GBP', 'EUR', 'GHS', 'KES', 'ZAR'],
                ],
                'paypal' => [
                    'name' => 'PayPal',
                    'enabled' => false,
                    'mode' => 'test',
                    'public_key' => env('PAYPAL_CLIENT_ID', ''),
                    'secret_key' => env('PAYPAL_CLIENT_SECRET', ''),
                    'webhook_secret' => env('PAYPAL_WEBHOOK_ID', ''),
                    'commission_rate' => 5.0,
                    'investment_fee_rate' => 3.0,
                    'supported_currencies' => ['USD', 'EUR', 'GBP'],
                ],
                'razorpay' => [
                    'name' => 'Razorpay',
                    'enabled' => false,
                    'mode' => 'test',
                    'public_key' => env('RAZORPAY_KEY_ID', ''),
                    'secret_key' => env('RAZORPAY_KEY_SECRET', ''),
                    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
                    'commission_rate' => 5.0,
                    'investment_fee_rate' => 3.0,
                    'supported_currencies' => ['INR'],
                ],
                'bank_transfer' => [
                    'name' => 'Bank Transfer',
                    'enabled' => false,
                    'mode' => 'manual',
                    'public_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => '',
                    'commission_rate' => 0.0,
                    'investment_fee_rate' => 0.0,
                    'supported_currencies' => ['USD', 'NGN', 'EUR', 'GBP'],
                    'bank_name' => '',
                    'account_name' => '',
                    'account_number' => '',
                    'routing_number' => '',
                    'swift_code' => '',
                    'instructions' => 'Include your order reference in the transfer description.',
                ],
            ],
        ];
    }

    private function mergeWithDefaults(array $stored): array
    {
        $defaults = $this->defaultGateways();

        if (empty($stored)) {
            return $defaults;
        }

        $merged = [
            'default_gateway' => $stored['default_gateway'] ?? $defaults['default_gateway'],
            'gateways' => [],
        ];

        foreach ($defaults['gateways'] as $id => $defaultGateway) {
            $storedGateway = $stored['gateways'][$id] ?? [];
            $mergedGateway = array_merge($defaultGateway, $storedGateway);

            // Never let empty stored values wipe env fallbacks for credential fields
            foreach (self::SENSITIVE_FIELDS as $field) {
                if (array_key_exists($field, $storedGateway) && ($storedGateway[$field] === '' || $storedGateway[$field] === null)) {
                    unset($mergedGateway[$field]);
                    $mergedGateway[$field] = $defaultGateway[$field] ?? '';
                }
            }

            if (array_key_exists('public_key', $storedGateway) && ($storedGateway['public_key'] === '' || $storedGateway['public_key'] === null)) {
                $mergedGateway['public_key'] = $defaultGateway['public_key'] ?? '';
            }

            $merged['gateways'][$id] = $mergedGateway;
        }

        return $merged;
    }

    private function mergeGatewayUpdates(array $current, array $input): array
    {
        $result = [
            'default_gateway' => $input['default_gateway'] ?? $current['default_gateway'],
            'gateways' => $current['gateways'],
        ];

        if (!isset($input['gateways']) || !is_array($input['gateways'])) {
            return $result;
        }

        foreach ($input['gateways'] as $id => $updates) {
            if (!isset($result['gateways'][$id])) {
                continue;
            }

            foreach ($updates as $field => $value) {
                if (in_array($field, self::SENSITIVE_FIELDS, true)) {
                    if ($this->isMaskedValue($value) || $value === '' || $value === null) {
                        continue;
                    }
                }
                $result['gateways'][$id][$field] = $value;
            }
        }

        return $result;
    }

    private function resolveGatewayCredentials(string $gateway, array $gatewayConfig): array
    {
        $defaults = $this->defaultGateways()['gateways'][$gateway] ?? [];

        foreach (self::SENSITIVE_FIELDS as $field) {
            if (empty($gatewayConfig[$field]) && !empty($defaults[$field])) {
                $gatewayConfig[$field] = $defaults[$field];
            }
        }

        if (empty($gatewayConfig['public_key']) && !empty($defaults['public_key'])) {
            $gatewayConfig['public_key'] = $defaults['public_key'];
        }

        return $gatewayConfig;
    }

    private function maskSensitiveValues(array $config): array
    {
        foreach ($config['gateways'] ?? [] as $id => $gateway) {
            foreach (self::SENSITIVE_FIELDS as $field) {
                if (!empty($gateway[$field])) {
                    $config['gateways'][$id][$field] = $this->maskValue($gateway[$field]);
                    $config['gateways'][$id]["{$field}_configured"] = true;
                } else {
                    $config['gateways'][$id]["{$field}_configured"] = false;
                }
            }
        }

        return $config;
    }

    private function maskValue(string $value): string
    {
        $length = strlen($value);
        if ($length <= 8) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', $length - 4) . substr($value, -4);
    }

    private function isMaskedValue(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return str_contains($value, '•') || str_contains($value, '●') || preg_match('/^[\*•●]+/', $value) === 1;
    }

    private function testStripe(array $config): array
    {
        $response = Http::withToken($config['secret_key'])
            ->get('https://api.stripe.com/v1/balance');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Stripe connection successful'];
        }

        return ['success' => false, 'message' => $response->json('error.message') ?? 'Stripe connection failed'];
    }

    private function testPaystack(array $config): array
    {
        $response = Http::withToken($config['secret_key'])
            ->get('https://api.paystack.co/balance');

        if ($response->successful() && $response->json('status')) {
            return ['success' => true, 'message' => 'Paystack connection successful'];
        }

        return ['success' => false, 'message' => $response->json('message') ?? 'Paystack connection failed'];
    }

    private function testFlutterwave(array $config): array
    {
        $response = Http::withToken($config['secret_key'])
            ->get('https://api.flutterwave.com/v3/balances/NGN');

        if ($response->successful() && $response->json('status') === 'success') {
            return ['success' => true, 'message' => 'Flutterwave connection successful'];
        }

        return ['success' => false, 'message' => $response->json('message') ?? 'Flutterwave connection failed'];
    }

    private function testPaypal(array $config): array
    {
        $baseUrl = ($config['mode'] ?? 'test') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::asForm()
            ->withBasicAuth($config['public_key'], $config['secret_key'])
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'PayPal connection successful'];
        }

        return ['success' => false, 'message' => 'PayPal authentication failed'];
    }

    private function testRazorpay(array $config): array
    {
        $response = Http::withBasicAuth($config['public_key'], $config['secret_key'])
            ->get('https://api.razorpay.com/v1/payments?count=1');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Razorpay connection successful'];
        }

        return ['success' => false, 'message' => $response->json('error.description') ?? 'Razorpay connection failed'];
    }
}
