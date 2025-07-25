<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission - BizInvestify</title>
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
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .field {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #1A73E8;
        }
        .field-label {
            font-weight: bold;
            color: #1A73E8;
            margin-bottom: 5px;
        }
        .field-value {
            color: #333;
        }
        .message-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-style: italic;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 12px;
        }
        .priority {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-general { background: #e3f2fd; color: #1976d2; }
        .priority-support { background: #fff3e0; color: #f57c00; }
        .priority-sales { background: #e8f5e8; color: #388e3c; }
        .priority-partnership { background: #f3e5f5; color: #7b1fa2; }
        .priority-press { background: #fce4ec; color: #c2185b; }
        .priority-other { background: #f5f5f5; color: #757575; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 New Contact Form Submission</h1>
        <p>Someone has submitted a message through the BizInvestify contact form</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="field-label">From:</div>
            <div class="field-value">{{ $first_name }} {{ $last_name }}</div>
        </div>

        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            </div>
        </div>

        @if($phone)
        <div class="field">
            <div class="field-label">Phone:</div>
            <div class="field-value">
                <a href="tel:{{ $phone }}">{{ $phone }}</a>
            </div>
        </div>
        @endif

        <div class="field">
            <div class="field-label">Subject:</div>
            <div class="field-value">
                <span class="priority priority-{{ $subject }}">{{ ucfirst($subject) }}</span>
            </div>
        </div>

        <div class="field">
            <div class="field-label">Message:</div>
            <div class="message-content">
                {{ $message }}
            </div>
        </div>

        <div class="field">
            <div class="field-label">Submitted:</div>
            <div class="field-value">{{ $submitted_at->format('F j, Y \a\t g:i A') }}</div>
        </div>

        <div class="field">
            <div class="field-label">IP Address:</div>
            <div class="field-value">{{ $ip_address }}</div>
        </div>
    </div>

    <div class="footer">
        <p>
            <strong>Quick Actions:</strong><br>
            <a href="mailto:{{ $email }}?subject=Re: {{ ucfirst($subject) }} Inquiry">Reply to {{ $first_name }}</a> |
            <a href="https://bizinvestify.com/admin/contacts">View All Contacts</a>
        </p>
        <p>This email was automatically generated from the BizInvestify contact form.</p>
    </div>
</body>
</html> 