<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            50% {
                transform: translateY(-100vh) translateX(50px) scale(1.2);
            }
        }

        .error-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .error-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .error-animation {
            margin: 40px 0;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            box-shadow: none;
            margin-left: 12px;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 640px) {
            .error-code {
                font-size: 80px;
            }
            .error-title {
                font-size: 24px;
            }
            .error-container {
                padding: 40px 24px;
            }
            .btn {
                display: block;
                margin: 8px 0;
            }
            .btn-secondary {
                margin-left: 0;
            }
        }

        @yield('custom-styles')
    </style>
</head>
<body>
    <!-- Animated particles -->
    <div class="particles">
        <div class="particle" style="width: 80px; height: 80px; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 100px; height: 100px; left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 70px; height: 70px; left: 50%; animation-delay: 1s;"></div>
        <div class="particle" style="width: 90px; height: 90px; left: 70%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 50px; height: 50px; left: 80%; animation-delay: 5s;"></div>
        <div class="particle" style="width: 110px; height: 110px; left: 90%; animation-delay: 2.5s;"></div>
    </div>

    <div class="error-container">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
