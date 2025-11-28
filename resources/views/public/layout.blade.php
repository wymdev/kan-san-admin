<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Kan San Application</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: white;
            border-radius: 50%;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: white;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            top: 60%;
            right: 10%;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 50%;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }
        
        /* Container */
        .container {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInDown 0.8s ease;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .tagline {
            color: #666;
            font-size: 16px;
        }
        
        /* Content Card */
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease;
        }
        
        h1 {
            font-size: 36px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 20px;
            animation: fadeIn 1s ease;
        }
        
        h2 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            animation: slideInLeft 0.8s ease;
        }
        
        h3 {
            font-size: 20px;
            font-weight: 600;
            color: #444;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        p {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }
        
        ul, ol {
            margin-left: 25px;
            margin-bottom: 20px;
        }
        
        li {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 10px;
        }
        
        strong {
            color: #333;
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #764ba2;
            transform: translateY(-2px);
        }
        
        .copyright {
            color: #666;
            font-size: 14px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header, .content-card, .footer {
                padding: 25px;
                border-radius: 15px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>
    
    <!-- Container -->
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">Kan San</div>
            <div class="tagline">Your Trusted Lottery Ticket Platform</div>
        </div>
        
        <!-- Content -->
        @yield('content')
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="{{ route('about-us') }}">About Us</a>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('terms-conditions') }}">Terms & Conditions</a>
            </div>
            <div class="copyright">
                © {{ date('Y') }} Kan San Application. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
