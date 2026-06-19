<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Investment Update - BizInvestify</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1A73E8 0%, #00C48C 100%);
            color: white;
            padding: 32px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 32px;
            border-radius: 0 0 10px 10px;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #00C48C;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 24px;
            background: #1A73E8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BizInvestify</h1>
        <p>{{ $role === 'seller' ? 'New Investment Received' : 'Investment Confirmed' }}</p>
    </div>
    <div class="content">
        <p>Hello {{ $user->first_name ?? $user->name }},</p>

        @if ($role === 'seller')
            <p>You received a new investment in <strong>{{ $business->name }}</strong>.</p>
        @else
            <p>Your investment in <strong>{{ $business->name }}</strong> has been confirmed.</p>
        @endif

        <div class="summary">
            <p><strong>Business:</strong> {{ $business->name }}</p>
            <p><strong>Amount:</strong> {{ $currency }} {{ number_format($amount, 2) }}</p>
        </div>

        <p>You can review the details in your dashboard.</p>

        <a href="{{ $dashboard_url }}" class="cta-button">View Investments</a>

        <p style="margin-top: 24px; color: #666; font-size: 14px;">
            If you did not authorize this transaction, please contact support immediately.
        </p>
    </div>
</body>
</html>
