<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code - BizInvestify</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: 600;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .otp-container {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .expires {
            color: #64748b;
            font-size: 14px;
            margin-top: 15px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .info-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h4 {
            color: #1e40af;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .info-box p {
            color: #1e40af;
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BizInvestify</div>
            <div class="title">Email Verification Code</div>
        </div>

        <div class="content">
            <p>Hello {{ $user->first_name }},</p>
            
            <p>Thank you for registering with BizInvestify! To complete your email verification, please use the verification code below:</p>

            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
                <div class="expires">This code expires at {{ $expires_at }}</div>
            </div>

            <div class="warning">
                <strong>Important:</strong> This verification code will expire in 10 minutes. If you don't verify your email within this time, you'll need to request a new code.
            </div>

            <div class="info-box">
                <h4>🔒 Security Notice</h4>
                <p>Never share this code with anyone. BizInvestify will never ask you for this code via phone, email, or any other communication method.</p>
            </div>

            <p>If you didn't create an account with BizInvestify, you can safely ignore this email.</p>
        </div>

        <div class="footer">
            <p>This email was sent to {{ $user->email }}</p>
            <p>&copy; {{ date('Y') }} BizInvestify. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html> 