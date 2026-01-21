<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 30%;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 20%;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        /* Main container */
        .error-container {
            position: relative;
            z-index: 1;
            background: white;
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Error code */
        .error-code {
            font-size: clamp(4rem, 15vw, 7rem);
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        /* Animation container */
        .error-animation {
            width: 100%;
            max-width: 200px;
            height: 200px;
            margin: 1.5rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Title and message */
        .error-title {
            font-size: clamp(1.5rem, 5vw, 2rem);
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }

        .error-message {
            font-size: clamp(0.875rem, 3vw, 1rem);
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Buttons */
        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 0.9375rem;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
        }

        /* Tablet and up */
        @media (min-width: 640px) {
            .error-container {
                padding: 3rem 2.5rem;
            }

            .error-actions {
                flex-direction: row;
                justify-content: center;
            }

            .btn {
                width: auto;
                min-width: 140px;
            }
        }

        /* Desktop */
        @media (min-width: 1024px) {
            .error-container {
                padding: 3.5rem 3rem;
                max-width: 550px;
            }
        }

        @yield('custom-styles')
    </style>
</head>

<body>
    <!-- Background shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="error-container">
        @yield('content')
    </div>

    @yield('scripts')
</body>

</html>