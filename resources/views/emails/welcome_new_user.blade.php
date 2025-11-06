<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Kan San Application</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.10);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 38px 20px 28px;
            color: #fff;
            text-align: center;
        }
        .header-icon {
            width: 70px; height: 70px; margin: 0 auto 18px;
            background: rgba(255,255,255,0.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 38px;
        }
        .header h1 { font-size: 27px; margin-bottom: 7px; font-weight: 700; }
        .header p { font-size: 14px; font-weight: 300; opacity: .95; }
        .content { padding: 38px 30px; }
        .welcome { font-size: 17px; margin-bottom: 21px; color: #333; font-weight: 500; }
        .account-section {
            background: #f7f8fa; border-left: 4px solid #667eea;
            padding: 24px; margin: 22px 0; border-radius: 8px;
        }
        .account-label { font-size: 12px; text-transform: uppercase; color: #999; margin-bottom: 12px; font-weight: 600; }
        .account-details {
            font-size: 16px; color: #333; font-family: 'Courier New', monospace;
            line-height: 1.9; letter-spacing: 1px;
        }
        .divider { height: 1px; background: #e8e8e8; margin: 21px 0; }
        .security-info {
            background: #eef5fa; border-left: 4px solid #2196F3;
            padding: 14px 20px; border-radius: 5px; margin: 18px 0;
        }
        .security-info p {
            font-size: 14px; color: #333; margin: 0;
        }
        .security-icon { display: inline-block; margin-right: 8px; font-size: 16px; }
        .note { font-size: 13px; color: #888; margin: 18px 0 0; }
        .action-section { margin: 25px 0 15px; text-align: center; }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff; padding: 13px 38px; border-radius: 8px;
            text-decoration: none; font-weight: 600; font-size: 14px;
            transition: transform .2s, box-shadow .2s;
        }
        .button:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(102,126,234,.15); }
        .footer {
            background: #f8f9fa; padding: 26px; border-top: 1px solid #e8e8e8; text-align: center;
            font-size: 12px; color: #666;
        }
        .footer-links { margin: 10px 0; }
        .footer-links a { color: #667eea; text-decoration: none; margin: 0 8px; }
        .footer-links a:hover { text-decoration: underline; }
        .company-info { margin-top: 12px; padding-top: 12px; border-top: 1px solid #e8e8e8; color: #999; }
        @media (max-width: 600px) {
            .email-container { border-radius: 0; }
            .content { padding: 18px 10px; }
            .header { padding: 17px 10px 14px; }
            .header h1 { font-size: 19px; }
            .account-details { font-size: 13px; letter-spacing: 0; }
            .button { width: 100%; padding: 15px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <div class="header-icon">👋</div>
        <h1>Welcome to Kan San Application!</h1>
        <p>Your new admin account has been securely created.</p>
    </div>
    <div class="content">
        <div class="welcome">
            Hi <strong>{{ $name }}</strong>,
        </div>
        <p>
            We're excited to welcome you as an administrator on our platform. Please find your account credentials below — keep your login details safe and never share them with anyone.
        </p>
        <div class="account-section">
            <div class="account-label">Your Account Credentials</div>
            <div class="account-details">
                <strong>Email:</strong> {{ $email }}<br>
                <strong>Password:</strong> {{ $password }}
            </div>
        </div>
        <div class="security-info">
            <p>
                <span class="security-icon">🔒</span>
                <strong>Important:</strong> For your security, <u>never share your login credentials with anyone</u>. The password is confidential and should only be used by you. We will <strong>never</strong> ask for your password via email, phone, or support.
            </p>
        </div>
        <div class="action-section">
            <a href="{{ $loginUrl ?? '#' }}" class="button">Login Now</a>
        </div>
        <div class="note">
            If you did not request this account or believe this was sent in error, please contact support immediately.
        </div>
    </div>
    <div class="footer">
        <div class="footer-links">
            <a href="#">Help Center</a>
            <a href="#">Contact Support</a>
            <a href="#">Privacy Policy</a>
        </div>
        <div class="company-info">
            © 2025 Kan San Application. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>
