<?php

/**
 * Quick payment gateway diagnostics.
 * Usage: php scripts/tests/test_payment_gateways.php
 */

$base = 'http://localhost:8000/api';

function request(string $method, string $url, ?array $body = null, ?string $token = null): array
{
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    curl_close($ch);

    return json_decode($raw, true) ?? ['raw' => $raw];
}

echo "=== BizInvestify Payment Gateway Diagnostics ===\n\n";

// 1. Login
$login = request('POST', "$base/login", [
    'email' => 'admin@example.com',
    'password' => 'Admin@123',
]);
$token = $login['data']['token'] ?? null;
echo $token ? "✓ Admin login OK\n" : "✗ Admin login FAILED: " . ($login['message'] ?? 'unknown') . "\n";

// 2. Public gateways
$public = request('GET', "$base/public/payment-gateways");
$methods = $public['data']['methods'] ?? [];
echo count($methods) ? "✓ Public gateways: " . implode(', ', array_column($methods, 'id')) . "\n" : "⚠ No gateways enabled (add API keys in Admin → Settings → Payment)\n";

// 3. Admin gateway config
if ($token) {
    $admin = request('GET', "$base/admin/payment-gateways", null, $token);
    $gateways = $admin['data']['gateways'] ?? [];
    foreach (['stripe', 'paystack', 'flutterwave'] as $gw) {
        $g = $gateways[$gw] ?? [];
        $configured = ($g['secret_key_configured'] ?? false) ? 'configured' : 'not configured';
        $enabled = ($g['enabled'] ?? false) ? 'enabled' : 'disabled';
        echo "  - {$gw}: {$enabled}, secret {$configured}\n";
    }

    foreach (['stripe', 'paystack', 'flutterwave', 'bank_transfer'] as $gw) {
        $test = request('POST', "$base/admin/payment-gateways/{$gw}/test", null, $token);
        $icon = ($test['success'] ?? false) ? '✓' : '✗';
        echo "{$icon} Test {$gw}: " . ($test['message'] ?? 'no response') . "\n";
    }
}

// 4. Callback routes
foreach (['/payments/paystack/callback', '/payments/flutterwave/callback'] as $path) {
    $code = 0;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo ($code >= 300 && $code < 400) ? "✓ Callback route {$path} responds (redirect)\n" : "✗ Callback {$path} HTTP {$code}\n";
}

echo "\nDone.\n";
