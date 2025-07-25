<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Email 2FA - BizInvestify</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .verification-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .warning h4 {
            color: #92400e;
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .warning p {
            color: #92400e;
            margin: 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            color: #64748b;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .expires {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .expires p {
            margin: 0;
            color: #dc2626;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BizInvestify</div>
            <div class="title">Setup Email Two-Factor Authentication</div>
            <div class="subtitle">Secure your account with email verification</div>
        </div>
        
        <div class="content">
            <p>Hello {{ $user->first_name }},</p>
            
            <p>You're setting up email-based two-factor authentication for your BizInvestify account. To complete the setup, please use the verification code below:</p>
            
            <div class="verification-code">
                {{ $verification_code }}
            </div>
            
            <div class="expires">
                <p><strong>⚠️ Important:</strong> This code will expire at {{ $expires_at }} (10 minutes from now)</p>
            </div>
            
            <div class="warning">
                <h4>Security Notice</h4>
                <p>• This code is only valid for 10 minutes<br>
                • Never share this code with anyone<br>
                • If you didn't request this setup, please contact support immediately</p>
            </div>
            
            <p>Once you enter this code in the setup page, your account will be protected with email-based two-factor authentication. You'll receive a verification code via email each time you log in.</p>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            The BizInvestify Team</p>
        </div>
        
        <div class="footer">
            <p>This email was sent to {{ $user->email }}</p>
            <p>&copy; {{ date('Y') }} BizInvestify. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html> 