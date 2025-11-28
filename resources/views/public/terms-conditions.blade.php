<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Kan San</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fafafa;
            color: #1a1a1a;
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
            opacity: 0.03;
            background-image: 
                radial-gradient(circle at 20% 50%, #ff6b6b 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, #4ecdc4 0%, transparent 50%);
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
            color: #ff6b6b;
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
            background: #ff6b6b;
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: #ff6b6b;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: #ff6b6b;
        }

        h1 {
            font-size: 56px;
            font-weight: 700;
            color: #1a1a1a;
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
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.05), rgba(78, 205, 196, 0.05));
            border-left: 4px solid #ff6b6b;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            animation: fadeInUp 0.8s ease;
        }

        .intro p {
            color: #444;
            margin: 0;
        }

        h2 {
            font-size: 28px;
            color: #1a1a1a;
            margin: 50px 0 20px;
            font-weight: 600;
            letter-spacing: -0.5px;
            padding-left: 20px;
            border-left: 4px solid #ff6b6b;
            opacity: 0;
            animation: slideInLeft 0.6s ease forwards;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        h3 {
            font-size: 20px;
            color: #333;
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

        ul, ol {
            margin: 20px 0 20px 25px;
        }

        ul li, ol li {
            color: #555;
            margin: 12px 0;
            padding-left: 15px;
            position: relative;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        ul li::before {
            content: '•';
            position: absolute;
            left: -15px;
            color: #ff6b6b;
            font-size: 20px;
            font-weight: bold;
        }

        ol li {
            padding-left: 0;
        }

        .section-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            transition: all 0.4s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .section-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(180deg, #ff6b6b, #4ecdc4);
            transition: height 0.4s ease;
        }

        .section-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-5px);
            border-color: #ff6b6b;
        }

        .section-card:hover::before {
            height: 100%;
        }

        .section-card h3 {
            margin-top: 0;
            opacity: 1;
            animation: none;
        }

        .section-card p, .section-card ul {
            opacity: 1;
            animation: none;
        }

        .section-card ul li {
            opacity: 1;
            animation: none;
        }

        .important-note {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(78, 205, 196, 0.1));
            border: 2px solid #ff6b6b;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            animation: fadeInUp 0.8s ease;
        }

        .important-note p {
            margin: 0;
            color: #333;
            font-weight: 500;
            opacity: 1;
            animation: none;
        }

        .contact-box {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            padding: 30px;
            margin: 40px 0;
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }

        .contact-box h3 {
            color: #ff6b6b;
            margin: 0 0 20px;
            opacity: 1;
            animation: none;
        }

        .contact-box p {
            margin: 10px 0;
            opacity: 1;
            animation: none;
        }

        strong {
            color: #ff6b6b;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 36px;
            }

            h2 {
                font-size: 24px;
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

            .section-card {
                padding: 20px;
            }
        }

        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4);
            z-index: 1000;
            transform-origin: left;
            transition: transform 0.1s ease;
        }

        /* Stagger animations */
        h2:nth-of-type(1) { animation-delay: 0.1s; }
        h2:nth-of-type(2) { animation-delay: 0.2s; }
        h3:nth-of-type(1) { animation-delay: 0.2s; }
        p:nth-of-type(1) { animation-delay: 0.3s; }
        .section-card:nth-of-type(1) { animation-delay: 0.1s; }
        .section-card:nth-of-type(2) { animation-delay: 0.2s; }
        .section-card:nth-of-type(3) { animation-delay: 0.3s; }
        ul li:nth-child(1) { animation-delay: 0.1s; }
        ul li:nth-child(2) { animation-delay: 0.15s; }
        ul li:nth-child(3) { animation-delay: 0.2s; }
        ul li:nth-child(4) { animation-delay: 0.25s; }
        ul li:nth-child(5) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="progress-bar" id="progressBar"></div>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- <nav>
            <div class="logo">KAN SAN</div>
            <div class="nav-links">
                <a href="about.html">About</a>
                <a href="#" class="active">Terms</a>
                <a href="privacy.html">Privacy</a>
            </div>
        </nav> -->

        <h1>Terms & Conditions</h1>
        <p class="last-updated">Last updated: November 28, 2025</p>

        <div class="intro">
            <p>Welcome to Kan San Application. By accessing or using our lottery ticket platform, you agree to be bound by these Terms and Conditions. Please read them carefully before using our services.</p>
        </div>

        <h2>1. Acceptance of Terms</h2>
        <p>By creating an account and using Kan San Application, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions, as well as our Privacy Policy. If you do not agree, please do not use our services.</p>

        <h2>2. Eligibility</h2>
        <p>To use our services, you must:</p>
        <ul>
            <li>Be at least 18 years of age</li>
            <li>Have the legal capacity to enter into binding contracts</li>
            <li>Reside in a jurisdiction where lottery participation is legal</li>
            <li>Provide accurate and complete registration information</li>
            <li>Comply with all applicable laws and regulations</li>
        </ul>

        <h2>3. Account Registration and Security</h2>
        
        <div class="section-card">
            <h3>3.1 Account Creation</h3>
            <p>You agree to:</p>
            <ul>
                <li>Provide accurate, current, and complete information during registration</li>
                <li>Maintain and promptly update your account information</li>
                <li>Keep your password confidential and secure</li>
                <li>Notify us immediately of any unauthorized access</li>
                <li>Be responsible for all activities under your account</li>
            </ul>
        </div>

        <div class="section-card">
            <h3>3.2 Account Restrictions</h3>
            <p>You may not:</p>
            <ul>
                <li>Create multiple accounts</li>
                <li>Share your account with others</li>
                <li>Use another person's account without permission</li>
                <li>Transfer or sell your account</li>
            </ul>
        </div>

        <h2>4. Lottery Ticket Purchases</h2>
        
        <div class="section-card">
            <h3>4.1 Purchase Process</h3>
            <ul>
                <li>All ticket purchases must be made through the app</li>
                <li>Payment must be submitted with a valid payment screenshot</li>
                <li>Purchases are subject to admin approval</li>
                <li>Approved tickets cannot be cancelled or refunded</li>
                <li>You are responsible for verifying ticket details before purchase</li>
            </ul>
        </div>

        <div class="section-card">
            <h3>4.2 Pricing and Payment</h3>
            <ul>
                <li>All prices are displayed in Thai Baht (THB)</li>
                <li>Prices are subject to change without notice</li>
                <li>Payment must be made in full before ticket approval</li>
                <li>We reserve the right to reject any purchase</li>
            </ul>
        </div>

        <h2>5. Draw Results and Winnings</h2>
        <p>Draw results are obtained from official lottery sources. We verify winning tickets against official results and notify winners promptly. Winners must claim prizes within the specified timeframe.</p>

        <h2>6. Prohibited Activities</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use the service for any illegal or unauthorized purpose</li>
            <li>Violate any laws in your jurisdiction</li>
            <li>Infringe on intellectual property rights</li>
            <li>Transmit viruses, malware, or harmful code</li>
            <li>Attempt to gain unauthorized access to our systems</li>
            <li>Engage in fraudulent activities</li>
        </ul>

        <h2>7. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, Kan San shall not be liable for any indirect, incidental, special, or consequential damages, loss of profits, revenue, data, or business opportunities.</p>

        <h2>8. Termination</h2>
        <p>We reserve the right to suspend or terminate your account at any time. You may terminate your account by contacting us. Upon termination, your right to use the service will immediately cease.</p>

        <h2>9. Modifications to Terms</h2>
        <p>We reserve the right to modify these Terms at any time. We will notify users of significant changes through the app or email. Continued use of the service after changes constitutes acceptance of the modified Terms.</p>

        <h2>10. Governing Law</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of Thailand. Any disputes arising from these Terms shall be resolved through good faith negotiation, mediation, or litigation in Thai courts.</p>

        <div class="important-note">
            <p>By using Kan San Application, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
        </div>

        <div class="contact-box">
            <h3>Questions About These Terms?</h3>
            <p><strong>Email:</strong> support@kansan.com</p>
            <!-- <p><strong>Phone:</strong> +66 XX XXX XXXX</p> -->
        </div>
    </div>

    <script>
        // Progress bar on scroll
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('progressBar').style.transform = `scaleX(${scrolled / 100})`;
        });
    </script>
</body>
</html>