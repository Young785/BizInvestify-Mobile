<?php

// Test script for Payment API endpoints
// Run with: php test_payment_api.php

$baseUrl = 'http://127.0.0.1:8000/api';

// Test data
$testData = [
    'amount' => 5000, // $50.00 in cents
    'currency' => 'usd',
    'business_id' => 1,
    'metadata' => [
        'investment_type' => 'equity',
        'test' => true
    ]
];

echo "🧪 Testing Payment API Endpoints\n";
echo "================================\n\n";

// Test 1: Create Payment Intent
echo "1. Testing Create Payment Intent...\n";
$response = curl_request("$baseUrl/payments/create-intent", 'POST', $testData);
echo "Response: " . $response . "\n\n";

// Test 2: Process Bank Transfer
echo "2. Testing Bank Transfer Processing...\n";
$bankTransferData = [
    'payment_intent' => 'pi_test_123456789',
    'amount' => 5000,
    'business_id' => 1,
    'transfer_type' => 'bank_transfer'
];
$response = curl_request("$baseUrl/payments/bank-transfer", 'POST', $bankTransferData);
echo "Response: " . $response . "\n\n";

// Test 3: Process Crypto Payment
echo "3. Testing Crypto Payment Processing...\n";
$cryptoData = [
    'payment_intent' => 'pi_test_123456789',
    'amount' => 5000,
    'business_id' => 1,
    'crypto_type' => 'bitcoin'
];
$response = curl_request("$baseUrl/payments/crypto", 'POST', $cryptoData);
echo "Response: " . $response . "\n\n";

// Test 4: Get Payment Methods
echo "4. Testing Get Payment Methods...\n";
$response = curl_request("$baseUrl/payments/methods", 'GET');
echo "Response: " . $response . "\n\n";

// Test 5: Get Transaction History
echo "5. Testing Get Transaction History...\n";
$response = curl_request("$baseUrl/payments/transactions", 'GET');
echo "Response: " . $response . "\n\n";

// Test 6: Get Payment Analytics
echo "6. Testing Get Payment Analytics...\n";
$response = curl_request("$baseUrl/payments/analytics", 'GET');
echo "Response: " . $response . "\n\n";

echo "✅ Payment API Testing Complete!\n";

function curl_request($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Add headers for authentication (you'll need a valid token)
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        // 'Authorization: Bearer YOUR_TOKEN_HERE' // Uncomment when you have a token
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "cURL Error: $error";
    }
    
    curl_close($ch);
    
    return "HTTP $httpCode: $response";
} 