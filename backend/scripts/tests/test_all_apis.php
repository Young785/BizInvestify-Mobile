<?php

/**
 * BizInvestify API smoke test script.
 * Usage: php scripts/tests/test_all_apis.php [base_url] [email] [password]
 */

$baseUrl = rtrim($argv[1] ?? 'http://localhost:8000/api', '/');
$email = $argv[2] ?? 'seller@example.com';
$password = $argv[3] ?? 'Seller@123';

function request(string $method, string $url, ?array $data = null, ?string $token = null): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer '.$token;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $status,
        'body' => json_decode($body ?: '{}', true),
        'raw' => $body,
    ];
}

function test(string $name, callable $fn): void
{
    echo "\n--- {$name} ---\n";
    try {
        $fn();
        echo "PASS\n";
    } catch (Throwable $e) {
        echo 'FAIL: '.$e->getMessage()."\n";
    }
}

function assertOk(array $response, int $expected = 200): void
{
    if ($response['status'] !== $expected) {
        throw new RuntimeException('HTTP '.$response['status'].' — '.($response['raw'] ?? ''));
    }
    if (isset($response['body']['success']) && $response['body']['success'] === false) {
        throw new RuntimeException($response['body']['message'] ?? 'API returned success=false');
    }
}

echo "BizInvestify API Tests\nBase: {$baseUrl}\n";

$token = null;

test('Login', function () use ($baseUrl, $email, $password, &$token) {
    global $baseUrl, $email, $password, $token;
    $res = request('POST', "{$baseUrl}/login", ['email' => $email, 'password' => $password]);
    assertOk($res);
    $token = $res['body']['data']['token'] ?? null;
    if (! $token) {
        throw new RuntimeException('No token in login response');
    }
    echo "Token acquired\n";
});

test('Profile', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/profile", null, $token);
    assertOk($res);
});

test('Products (user)', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/products/user", null, $token);
    assertOk($res);
});

test('Businesses (user)', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/businesses/user", null, $token);
    assertOk($res);
});

test('Categories', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/categories", null, $token);
    assertOk($res);
});

test('Transactions', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/transactions", null, $token);
    assertOk($res);
});

test('Wallet summary', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/wallet/summary", null, $token);
    assertOk($res);
    echo 'Balance: '.($res['body']['data']['balance'] ?? 'N/A')."\n";
});

test('Bank accounts list', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/wallet/bank-accounts", null, $token);
    assertOk($res);
});

$bankAccountId = null;

test('Add bank account', function () use ($baseUrl, &$token, &$bankAccountId) {
    $res = request('POST', "{$baseUrl}/wallet/bank-accounts", [
        'bank_name' => 'Test Bank',
        'account_name' => 'Test Seller',
        'account_number' => '1234567890',
        'routing_number' => '021000021',
        'account_type' => 'checking',
        'is_default' => true,
    ], $token);
    assertOk($res, 201);
    $bankAccountId = $res['body']['data']['id'] ?? null;
    echo "Bank account ID: {$bankAccountId}\n";
});

test('Withdrawals list', function () use ($baseUrl, &$token) {
    $res = request('GET', "{$baseUrl}/wallet/withdrawals", null, $token);
    assertOk($res);
});

test('Public products', function () use ($baseUrl) {
    $res = request('GET', "{$baseUrl}/public/products?per_page=3");
    assertOk($res);
});

if ($bankAccountId) {
    test('Delete test bank account', function () use ($baseUrl, &$token, $bankAccountId) {
        $res = request('DELETE', "{$baseUrl}/wallet/bank-accounts/{$bankAccountId}", null, $token);
        assertOk($res);
    });
}

echo "\nAll tests completed.\n";
