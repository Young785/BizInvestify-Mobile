<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank you for contacting BizInvestify</title>
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
            padding: 40px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 40px;
            border-radius: 0 0 10px 10px;
        }
        .message-summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid #00C48C;
        }
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #1A73E8 0%, #00C48C 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .info-card h3 {
            color: #1A73E8;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
            color: #666;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #1A73E8;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .header, .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Message Received!</h1>
        <p>Thank you for reaching out to BizInvestify</p>
    </div>

    <div class="content">
        <p>Hi {{ $first_name }},</p>
        
        <p>Thank you for contacting us! We've successfully received your message and our team will review it shortly.</p>

        <div class="message-summary">
            <h3>📩 Your Message Summary</h3>
            <p><strong>Subject:</strong> {{ ucfirst($subject) }}</p>
            <p><strong>Submitted:</strong> {{ $submitted_at->format('F j, Y \a\t g:i A') }}</p>
            @if($phone)
            <p><strong>Phone:</strong> {{ $phone }}</p>
            @endif
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>⏱️ Response Time</h3>
                <p>We typically respond within <strong>24 hours</strong> during business days.</p>
            </div>
            <div class="info-card">
                <h3>📞 Urgent Matters</h3>
                <p>For urgent inquiries, call us at <strong>+1 (555) 123-4567</strong></p>
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <p>While you wait, why not explore what BizInvestify has to offer?</p>
            <a href="https://bizinvestify.com/features" class="cta-button">
                🚀 Explore Features
            </a>
        </div>

        <div style="background: white; padding: 25px; border-radius: 10px; margin: 25px 0;">
            <h3 style="color: #1A73E8; text-align: center; margin-bottom: 20px;">🌟 What Makes BizInvestify Special?</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0; padding-left: 25px; position: relative;">
                    <span style="position: absolute; left: 0; color: #00C48C;">✓</span>
                    AI-powered investor matching
                </li>
                <li style="margin: 10px 0; padding-left: 25px; position: relative;">
                    <span style="position: absolute; left: 0; color: #00C48C;">✓</span>
                    Bank-grade security for all transactions
                </li>
                <li style="margin: 10px 0; padding-left: 25px; position: relative;">
                    <span style="position: absolute; left: 0; color: #00C48C;">✓</span>
                    24/7 expert support team
                </li>
                <li style="margin: 10px 0; padding-left: 25px; position: relative;">
                    <span style="position: absolute; left: 0; color: #00C48C;">✓</span>
                    Over $50M in successful investments facilitated
                </li>
            </ul>
        </div>

        <p>If you have any immediate questions or concerns, don't hesitate to reach out to us:</p>
        <ul>
            <li>📧 Email: <a href="mailto:hello@bizinvestify.com">hello@bizinvestify.com</a></li>
            <li>📞 Phone: <a href="tel:+1-555-123-4567">+1 (555) 123-4567</a></li>
            <li>💬 Live Chat: Available on our website 24/7</li>
        </ul>
    </div>

    <div class="footer">
        <h3 style="color: #1A73E8;">Follow Us</h3>
        <div class="social-links">
            <a href="#">Twitter</a> |
            <a href="#">LinkedIn</a> |
            <a href="#">Facebook</a>
        </div>
        
        <p>
            <strong>BizInvestify</strong><br>
            123 Innovation Drive<br>
            San Francisco, CA 94105<br>
            <a href="https://bizinvestify.com">www.bizinvestify.com</a>
        </p>
        
        <p style="font-size: 12px; color: #999; margin-top: 20px;">
            This email was sent because you submitted a contact form on BizInvestify.<br>
            If you didn't submit this form, please <a href="mailto:security@bizinvestify.com">contact our security team</a>.
        </p>
    </div>
</body>
</html> 