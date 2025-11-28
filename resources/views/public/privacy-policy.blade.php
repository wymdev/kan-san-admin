<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Kan San</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f0ff;
            color: #2d2d2d;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.04;
            background-image: 
                radial-gradient(circle at 30% 40%, #8b5cf6 0%, transparent 50%),
                radial-gradient(circle at 70% 60%, #ec4899 0%, transparent 50%);
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.05;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            background: #8b5cf6;
            border-radius: 50%;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            background: #ec4899;
            border-radius: 30%;
            top: 60%;
            right: 15%;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            background: #8b5cf6;
            border-radius: 20%;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-30px) rotate(5deg);
            }
            50% {
                transform: translateY(-50px) rotate(-5deg);
            }
            75% {
                transform: translateY(-30px) rotate(3deg);
            }
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 0;
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: #666;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #8b5cf6, #ec4899);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: #8b5cf6;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: #8b5cf6;
        }

        h1 {
            font-size: 56px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            letter-spacing: -2px;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .last-updated {
            color: #999;
            font-size: 14px;
            margin-bottom: 40px;
            font-style: italic;
            animation: fadeInUp 1s ease;
        }

        .intro {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.1);
            animation: fadeInUp 0.8s ease;
            border: 1px solid rgba(139, 92, 246, 0.1);
        }

        .intro p {
            color: #444;
            margin: 0;
        }

        h2 {
            font-size: 32px;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 50px 0 25px;
            font-weight: 700;
            letter-spacing: -1px;
            opacity: 0;
            animation: slideInRight 0.6s ease forwards;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        h3 {
            font-size: 20px;
            color: #8b5cf6;
            margin: 30px 0 15px;
            font-weight: 600;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        p {
            color: #555;
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.8;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .info-card {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(139, 92, 246, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            animation: scaleIn 0.6s ease forwards;
            cursor: pointer;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .info-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.2);
            border-color: rgba(139, 92, 246, 0.3);
        }

        .info-card h3 {
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 1;
            animation: none;
        }

        .info-card .icon {
            font-size: 24px;
        }

        .info-card p, .info-card ul {
            opacity: 1;
            animation: none;
        }

        .info-card ul {
            list-style: none;
            padding: 0;
            margin: 15px 0 0;
        }

        .info-card ul li {
            padding: 8px 0;
            color: #666;
            position: relative;
            padding-left: 25px;
            opacity: 1;
            animation: none;
        }

        .info-card ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #8b5cf6;
            font-weight: bold;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        ul li {
            color: #555;
            margin: 15px 0;
            padding-left: 30px;
            position: relative;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #8b5cf6;
            font-size: 18px;
            font-weight: bold;
        }

        .highlight-box {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
            border-left: 4px solid #8b5cf6;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            animation: fadeInUp 0.8s ease;
        }

        .highlight-box p {
            margin: 0;
            color: #444;
            font-weight: 500;
            opacity: 1;
            animation: none;
        }

        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .security-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .security-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.15);
        }

        .security-item .icon {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

        .security-item h4 {
            color: #8b5cf6;
            margin: 10px 0;
            font-size: 16px;
        }

        .security-item p {
            margin: 0;
            font-size: 14px;
            color: #666;
            opacity: 1;
            animation: none;
        }

        .contact-section {
            background: linear-gradient(135deg, #87858eff, #f6dce9ff);
            border-radius: 20px;
            padding: 40px;
            margin: 50px 0;
            text-align: center;
            color: #fff;
            animation: fadeInUp 0.8s ease;
        }

        .contact-section h2 {
            color: #fff;
            margin: 0 0 20px;
            opacity: 1;
            animation: none;
            -webkit-text-fill-color: #fff;
        }

        .contact-section p {
            margin: 12px 0;
            font-size: 16px;
            opacity: 1;
            animation: none;
        }

        strong {
            color: #8b5cf6;
            font-weight: 600;
        }

        .contact-section strong {
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 36px;
            }

            h2 {
                font-size: 26px;
            }

            h3 {
                font-size: 18px;
            }

            nav {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .security-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Stagger animations */
        h2:nth-of-type(1) { animation-delay: 0.1s; }
        h2:nth-of-type(2) { animation-delay: 0.2s; }
        h3:nth-of-type(1) { animation-delay: 0.2s; }
        p:nth-of-type(1) { animation-delay: 0.3s; }
        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }
        .info-card:nth-child(4) { animation-delay: 0.4s; }
        .security-item:nth-child(1) { animation-delay: 0.1s; }
        .security-item:nth-child(2) { animation-delay: 0.2s; }
        .security-item:nth-child(3) { animation-delay: 0.3s; }
        .security-item:nth-child(4) { animation-delay: 0.4s; }
        ul li:nth-child(1) { animation-delay: 0.1s; }
        ul li:nth-child(2) { animation-delay: 0.15s; }
        ul li:nth-child(3) { animation-delay: 0.2s; }
        ul li:nth-child(4) { animation-delay: 0.25s; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container">
        <!-- <nav>
            <div class="logo">KAN SAN</div>
            <div class="nav-links">
                <a href="about.html">About</a>
                <a href="terms.html">Terms</a>
                <a href="#" class="active">Privacy</a>
            </div>
        </nav> -->

        <h1>Privacy Policy</h1>
        <p class="last-updated">Last updated: November 28, 2025</p>

        <div class="intro">
            <p>At Kan San Application, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our lottery ticket platform.</p>
        </div>

        <h2>1. Information We Collect</h2>

        <div class="info-grid">
            <div class="info-card">
                <h3><span class="icon">👤</span> Personal Info</h3>
                <ul>
                    <li>Contact details</li>
                    <li>Identity information</li>
                    <li>Account credentials</li>
                    <li>Transaction data</li>
                </ul>
            </div>

            <div class="info-card">
                <h3><span class="icon">📱</span> Device Info</h3>
                <ul>
                    <li>Device ID and type</li>
                    <li>Push tokens</li>
                    <li>App version</li>
                    <li>Location data</li>
                </ul>
            </div>
        </div>

        <h2>2. How We Use Your Information</h2>

        <div class="info-grid">
            <div class="info-card">
                <h3><span class="icon">🎯</span> Service Delivery</h3>
                <p>Process lottery ticket purchases, manage your account, and deliver purchased tickets efficiently.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">💬</span> Communication</h3>
                <p>Send order confirmations, draw results, winning notifications, and important updates.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">🛡️</span> Security</h3>
                <p>Verify your identity, prevent fraud, and protect against unauthorized access.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">📈</span> Improvement</h3>
                <p>Analyze usage patterns to enhance our services and user experience.</p>
            </div>
        </div>

        <h2>3. Data Sharing</h2>
        <p>We do not sell your personal information. We may share your data only in these circumstances:</p>
        <ul>
            <li>Trusted service providers who help us operate our platform</li>
            <li>When required by law, court order, or government regulations</li>
            <li>In case of merger, acquisition, or sale of assets</li>
            <li>When you explicitly authorize us to share your information</li>
        </ul>

        <h2>4. Data Security</h2>
        <p>We implement industry-standard security measures to protect your information:</p>

        <div class="security-grid">
            <div class="security-item">
                <span class="icon">🔐</span>
                <h4>Encryption</h4>
                <p>All sensitive data is encrypted</p>
            </div>

            <div class="security-item">
                <span class="icon">🔒</span>
                <h4>HTTPS</h4>
                <p>Secure connections</p>
            </div>

            <div class="security-item">
                <span class="icon">🛡️</span>
                <h4>Audits</h4>
                <p>Regular security checks</p>
            </div>

            <div class="security-item">
                <span class="icon">🔑</span>
                <h4>Access Control</h4>
                <p>Strict authentication</p>
            </div>
        </div>

        <h2>5. Your Rights</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3><span class="icon">👁️</span> Access</h3>
                <p>Request a copy of your personal information at any time.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">✏️</span> Correction</h3>
                <p>Update or correct inaccurate information in your account.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">🗑️</span> Deletion</h3>
                <p>Request deletion of your account and associated data.</p>
            </div>

            <div class="info-card">
                <h3><span class="icon">📤</span> Portability</h3>
                <p>Request your data in a portable, machine-readable format.</p>
            </div>
        </div>

        <h2>6. Push Notifications</h2>
        <div class="highlight-box">
            <p>We use push notifications to notify you of draw results, winning tickets, order status updates, and important announcements. You can disable push notifications in your device settings at any time.</p>
        </div>

        <h2>7. Data Retention</h2>
        <p>We retain your personal information for as long as your account is active or as needed to provide services. After account deletion, we may retain certain information for legal compliance, dispute resolution, and fraud prevention.</p>

        <h2>8. Children's Privacy</h2>
        <p>Our services are intended for users 18 years and older. We do not knowingly collect information from children under 18. If you believe we have collected such information, please contact us immediately.</p>

        <div class="contact-section">
            <h2>Contact Us About Privacy</h2>
            <p><strong>Email:</strong> privacy@kansan.com</p>
            <p style="margin-top: 20px; font-size: 14px; opacity: 0.9;">If you have questions or concerns about this Privacy Policy or our data practices, please don't hesitate to reach out.</p>
        </div>
    </div>
</body>
</html>