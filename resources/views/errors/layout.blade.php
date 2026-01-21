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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Grid background */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid 20s linear infinite;
        }

        @keyframes grid {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(50px);
            }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .error-code {
            font-size: clamp(6rem, 20vw, 12rem);
            font-weight: 900;
            line-height: 0.9;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            letter-spacing: -0.05em;
            animation: glow 3s ease-in-out infinite;
        }

        @keyframes glow {

            0%,
            100% {
                filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.5));
            }

            50% {
                filter: drop-shadow(0 0 40px rgba(139, 92, 246, 0.8));
            }
        }

        .icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: rgba(99, 102, 241, 0.1);
            border: 2px solid rgba(99, 102, 241, 0.3);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            position: relative;
        }

        .icon-wrapper::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);
            border-radius: 24px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .icon-wrapper:hover::before {
            opacity: 0.2;
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        p {
            font-size: clamp(1rem, 2vw, 1.125rem);
            color: #94a3b8;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.5);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px -10px rgba(99, 102, 241, 0.7);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 640px) {
            .actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @yield('custom-styles')
    </style>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>

</html>