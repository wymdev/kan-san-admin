<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        
        .header-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 300;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .otp-section {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        
        .otp-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        
        .otp-info {
            font-size: 12px;
            color: #666;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .timer-icon {
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background: #e8e8e8;
            margin: 25px 0;
        }
        
        .security-info {
            background: #f0f7ff;
            border-left: 4px solid #2196F3;
            padding: 15px 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .security-info p {
            font-size: 13px;
            color: #333;
            line-height: 1.6;
        }
        
        .security-icon {
            display: inline-block;
            margin-right: 8px;
            font-size: 16px;
        }
        
        .action-section {
            margin: 30px 0;
            text-align: center;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .footer {
            background: #f8f9fa;
            padding: 30px;
            border-top: 1px solid #e8e8e8;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .footer-links {
            margin: 15px 0;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .company-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e8e8e8;
            color: #999;
        }
        
        @media (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .otp-code {
                font-size: 32px;
                letter-spacing: 4px;
            }
            
            .button {
                display: block;
                width: 100%;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">🔐</div>
            <h1>Verify Your Identity</h1>
            <p>Your one-time password is ready</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello,</p>
            
            <p class="greeting">
                We received a request to verify your account. Use the code below to confirm your identity and complete your login.
            </p>
            
            <!-- OTP Code Section -->
            <div class="otp-section">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-info">
                    <span class="timer-icon">⏱️</span>
                    <span>Expires in 10 minutes</span>
                </div>
            </div>
            
            <!-- Security Info -->
            <div class="security-info">
                <p>
                    <span class="security-icon">ℹ️</span>
                    <strong>Never share this code.</strong> We will never ask you for this code via email, phone, or support.
                </p>
            </div>
            
            <!-- Additional Info -->
            <p class="greeting" style="font-size: 14px; color: #666;">
                If you didn't request this verification code, you can safely ignore this email. Your account remains secure.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="#">Help Center</a>
                <a href="#">Contact Support</a>
                <a href="#">Privacy Policy</a>
            </div>
            
            <div class="company-info">
                <p>© 2025 Kan San Application. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
