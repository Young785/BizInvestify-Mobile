<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - BizInvestify</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: inline-block;
        }

        .logo-icon {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 10px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            color: #ffffff;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .content {
            padding: 50px 40px;
            background: #ffffff;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            color: #475569;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 18px 50px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .button:hover::before {
            left: 100%;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .warning::before {
            content: '⚠️';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 40px;
            opacity: 0.2;
        }

        .warning-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .warning-text {
            color: #92400e;
            font-size: 14px;
            line-height: 1.6;
        }

        .link-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border: 1px solid #e2e8f0;
        }

        .link-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .link-url {
            word-break: break-all;
            color: #667eea;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .note {
            font-size: 14px;
            color: #64748b;
            font-style: italic;
            margin-top: 30px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .footer {
            background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
            padding: 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-text {
            color: #64748b;
            font-size: 14px;
            margin: 8px 0;
        }

        .footer-email {
            color: #667eea;
            font-weight: 500;
        }

        .social-links {
            margin: 25px 0 20px;
            padding: 0;
        }

        .social-link {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 50%;
            margin: 0 8px;
            line-height: 36px;
            color: #667eea;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 25px 0;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .header {
                padding: 40px 20px;
            }

            .content {
                padding: 30px 20px;
            }

            .footer {
                padding: 30px 20px;
            }

            .title {
                font-size: 24px;
            }

            .button {
                padding: 16px 40px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="container">
            <!-- Header Section -->
            <div class="header">
                <div class="logo-container">
                    <div class="logo-icon">💼</div>
                    <div class="logo">BizInvestify</div>
                </div>
                <div class="title">Verify Your Email Address</div>
            </div>

            <!-- Content Section -->
            <div class="content">
                <div class="greeting">Hello {{ $user->first_name }}! 👋</div>

                <p class="message">
                    Thank you for joining <strong>BizInvestify</strong>! We're excited to have you on board.
                    To complete your registration and unlock all features, please verify your email address by clicking
                    the button below.
                </p>

                <div class="button-container">
                    <a href="{{ $verification_url }}" class="button" style="color: #ffffff">
                        ✓ Verify Email Address
                    </a>
                </div>

                <div class="warning">
                    <div class="warning-title">⏰ Time-Sensitive Action Required</div>
                    <div class="warning-text">
                        This verification link will expire in <strong>24 hours</strong>. If you don't verify your email
                        within this time, you'll need to request a new verification link.
                    </div>
                </div>

                <div class="link-section">
                    <div class="link-label">Having trouble with the button? Copy and paste this link:</div>
                    <div class="link-url">{{ $verification_url }}</div>
                </div>

                <div class="note">
                    💡 <strong>Didn't create an account?</strong> If you didn't sign up for BizInvestify, you can safely
                    ignore this email. No further action is required.
                </div>
            </div>

            <!-- Footer Section -->
            <div class="footer">
                <div class="footer-text">This email was sent to <span class="footer-email">{{ $user->email }}</span>
                </div>

                <div class="divider"></div>

                <div class="social-links">
                    <a href="#" class="social-link">𝕏</a>
                    <a href="#" class="social-link">in</a>
                    <a href="#" class="social-link">f</a>
                </div>

                <div class="footer-text">&copy; {{ date('Y') }} BizInvestify. All rights reserved.</div>
                <div class="footer-text">Need help? Contact our support team anytime.</div>
            </div>
        </div>
    </div>
</body>

</html>