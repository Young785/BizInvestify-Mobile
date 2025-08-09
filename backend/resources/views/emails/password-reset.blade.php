<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - BizInvestify</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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
        .tagline {
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }
        .warning {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .warning-title {
            color: #c53030;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .warning-text {
            color: #742a2a;
            font-size: 14px;
        }
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            color: #718096;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        .expiry-notice {
            background-color: #fef5e7;
            border: 1px solid #f6ad55;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #744210;
        }
        .manual-link {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BizInvestify</div>
            <div class="tagline">Your Trusted Business Investment Platform</div>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $user->first_name }}!</div>
            
            <div class="message">
                We received a request to reset your password for your BizInvestify account. 
                If you didn't make this request, you can safely ignore this email.
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">
                    Reset Your Password
                </a>
            </div>
            
            <div class="expiry-notice">
                <strong>⚠️ Important:</strong> This password reset link will expire in 24 hours for security reasons.
            </div>
            
            <div class="warning">
                <div class="warning-title">🔒 Security Notice</div>
                <div class="warning-text">
                    If you didn't request this password reset, please contact our support team immediately. 
                    Your account security is our top priority.
                </div>
            </div>
            
            <div class="message">
                If the button above doesn't work, you can copy and paste the following link into your browser:
            </div>
            
            <div class="manual-link">
                {{ $resetUrl }}
            </div>
            
            <div class="message">
                After resetting your password, you'll be able to log in to your account with your new password.
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-text">
                This email was sent to {{ $user->email }} because a password reset was requested for your BizInvestify account.
            </div>
            
            <div class="footer-text">
                © {{ date('Y') }} BizInvestify. All rights reserved.
            </div>
            
            <div class="social-links">
                <a href="#">Privacy Policy</a> |
                <a href="#">Terms of Service</a> |
                <a href="#">Support</a>
            </div>
        </div>
    </div>
</body>
</html> 