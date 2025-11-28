<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Kan San</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
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
                radial-gradient(circle at 20% 50%, #00d4ff 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, #7c3aed 0%, transparent 50%);
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
            color: #00d4ff;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: #888;
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
            background: #00d4ff;
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: #00d4ff;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: #00d4ff;
        }

        h1 {
            font-size: 56px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
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

        .subtitle {
            font-size: 20px;
            color: #00d4ff;
            margin-bottom: 60px;
            font-weight: 500;
            animation: fadeInUp 1s ease;
        }

        h2 {
            font-size: 32px;
            color: #fff;
            margin: 60px 0 30px;
            font-weight: 600;
            letter-spacing: -1px;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        p {
            color: #b0b0b0;
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.8;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 30px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(124, 58, 237, 0.1));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(0, 212, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 212, 255, 0.1);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 15px;
            display: block;
            position: relative;
            z-index: 1;
        }

        .feature-card h3 {
            font-size: 20px;
            color: #fff;
            margin-bottom: 10px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .feature-card p {
            font-size: 14px;
            color: #999;
            line-height: 1.6;
            margin: 0;
            position: relative;
            z-index: 1;
            opacity: 1;
        }

        .highlight-box {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.05), rgba(124, 58, 237, 0.05));
            border-left: 3px solid #00d4ff;
            border-radius: 12px;
            padding: 30px;
            margin: 40px 0;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .highlight-box h3 {
            color: #00d4ff;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .highlight-box p {
            margin: 0;
            opacity: 1;
        }

        .cta-section {
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            margin: 60px 0;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .cta-section h2 {
            color: #fff;
            font-size: 36px;
            margin: 0 0 15px;
            position: relative;
            z-index: 1;
            opacity: 1;
            animation: none;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.9);
            margin: 10px 0;
            position: relative;
            z-index: 1;
            opacity: 1;
            animation: none;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .contact-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(0, 212, 255, 0.3);
            transform: translateY(-5px);
        }

        .contact-item strong {
            color: #00d4ff;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .contact-item span {
            color: #b0b0b0;
            font-size: 15px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            color: #b0b0b0;
            padding: 12px 0;
            padding-left: 30px;
            position: relative;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #00d4ff;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 36px;
            }

            .subtitle {
                font-size: 18px;
            }

            h2 {
                font-size: 28px;
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

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .cta-section {
                padding: 35px 25px;
            }

            .cta-section h2 {
                font-size: 28px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        .scroll-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: rgba(0, 212, 255, 0.1);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .scroll-indicator:hover {
            background: rgba(0, 212, 255, 0.2);
            transform: translateY(-5px);
        }

        /* Stagger animations */
        h2:nth-of-type(1) { animation-delay: 0.2s; }
        p:nth-of-type(1) { animation-delay: 0.3s; }
        p:nth-of-type(2) { animation-delay: 0.4s; }
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        .feature-card:nth-child(5) { animation-delay: 0.5s; }
        ul li:nth-child(1) { animation-delay: 0.1s; }
        ul li:nth-child(2) { animation-delay: 0.2s; }
        ul li:nth-child(3) { animation-delay: 0.3s; }
        ul li:nth-child(4) { animation-delay: 0.4s; }
        ul li:nth-child(5) { animation-delay: 0.5s; }
        .contact-item:nth-child(1) { animation-delay: 0.1s; }
        .contact-item:nth-child(2) { animation-delay: 0.2s; }
        .contact-item:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- <nav>
            <div class="logo">KAN SAN</div>
            <div class="nav-links">
                <a href="#" class="active">About</a>
                <a href="terms.html">Terms</a>
                <a href="privacy.html">Privacy</a>
            </div>
        </nav> -->

        <h1>About Kan San</h1>
        <p class="subtitle">Your Trusted Partner in Lottery Ticket Management</p>

        <h2>Who We Are</h2>
        <p>Kan San Application is a modern, user-friendly platform designed to revolutionize the way people purchase and manage lottery tickets. We combine cutting-edge technology with exceptional customer service to provide a seamless lottery experience.</p>
        
        <p>Founded with the vision of making lottery participation more accessible, secure, and convenient, we have grown to become a trusted name in the digital lottery space.</p>

        <div class="highlight-box">
            <h3>Our Mission</h3>
            <p>To provide a safe, transparent, and convenient platform that makes lottery ticket purchasing accessible to everyone, while maintaining the highest standards of security and customer service.</p>
        </div>

        <h2>What We Do</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-icon">🎫</span>
                <h3>Ticket Sales</h3>
                <p>Wide selection of lottery tickets available for purchase through our mobile application.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">📱</span>
                <h3>Mobile-First</h3>
                <p>Intuitive iOS and Android apps with real-time updates and push notifications.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <h3>Secure Transactions</h3>
                <p>Bank-level security with admin approval system to ensure authenticity.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">✓</span>
                <h3>Auto Verification</h3>
                <p>Automatic ticket checking against official results with instant notifications.</p>
            </div>

            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <h3>Purchase History</h3>
                <p>Track all purchases, view winning history, and access detailed statistics.</p>
            </div>
        </div>

        <h2>Why Choose Us</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-icon">🛡️</span>
                <h3>Security First</h3>
                <p>Bank-level encryption and security protocols protect your data.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">⚡</span>
                <h3>Fast & Reliable</h3>
                <p>Quick processing and reliable service you can count on.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">💎</span>
                <h3>Transparent</h3>
                <p>Clear pricing and honest communication at every step.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">✨</span>
                <h3>User-Friendly</h3>
                <p>Intuitive interface for users of all ages and abilities.</p>
            </div>
        </div>

        <h2>Our Commitment</h2>
        <ul>
            <li>Providing exceptional service and support to all users</li>
            <li>Continuously improving with new features and technologies</li>
            <li>Operating with honesty, transparency, and ethical practices</li>
            <li>Protecting your personal information and ensuring safe transactions</li>
            <li>Making lottery participation easy and convenient for everyone</li>
        </ul>

        <div class="cta-section">
            <h2>Download Kan San Today</h2>
            <p>Available on iOS and Android</p>
            <p style="font-size: 14px; margin-top: 15px;">Start your lottery journey with confidence and convenience</p>
        </div>

        <h2>Contact Us</h2>
        <p>We'd love to hear from you! Whether you have questions, feedback, or need support, our team is here to help.</p>
        
        <div class="contact-grid">
            <div class="contact-item">
                <strong>📧 Email</strong>
                <span>support@kansan.com</span>
            </div>
            
            <!-- <div class="contact-item">
                <strong>📱 Phone</strong>
                <span>+66 XX XXX XXXX</span>
            </div> -->
            
            <div class="contact-item">
                <strong>⏰ Support Hours</strong>
                <span>Mon-Sun, 9AM-6PM</span>
            </div>
        </div>
    </div>

    <div class="scroll-indicator" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        ↑
    </div>
</body>
</html>