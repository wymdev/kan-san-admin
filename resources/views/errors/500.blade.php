@extends('errors.layout')

@section('title', '500 - Server Error')

@section('custom-styles')
    <style>
        .server-icon {
            width: 120px;
            height: 140px;
            margin: 0 auto;
            position: relative;
        }

        .server-box {
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            position: relative;
            animation: glitch 2s ease-in-out infinite;
        }

        .server-light {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: absolute;
            left: 15px;
        }

        .server-light:nth-child(1) {
            top: 20px;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: blink 1s ease-in-out infinite;
        }

        .server-light:nth-child(2) {
            top: 40px;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: blink 1s ease-in-out infinite 0.3s;
        }

        .server-light:nth-child(3) {
            top: 60px;
            background: #f59e0b;
            box-shadow: 0 0 10px #f59e0b;
            animation: blink 1s ease-in-out infinite 0.6s;
        }

        .crack {
            width: 2px;
            height: 60px;
            background: rgba(255, 255, 255, 0.4);
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%) rotate(15deg);
        }

        @keyframes glitch {

            0%,
            90%,
            100% {
                transform: translate(0, 0);
            }

            92% {
                transform: translate(-2px, 0) skew(1deg);
            }

            94% {
                transform: translate(2px, 0) skew(-1deg);
            }

            96% {
                transform: translate(-2px, 0) skew(1deg);
            }

            98% {
                transform: translate(2px, 0) skew(-1deg);
            }
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }
    </style>
@endsection

@section('content')
    <div class="error-code">500</div>

    <div class="error-animation">
        <div class="server-icon">
            <div class="server-box">
                <div class="server-light"></div>
                <div class="server-light"></div>
                <div class="server-light"></div>
                <div class="crack"></div>
            </div>
        </div>
    </div>

    <h1 class="error-title">Internal Server Error</h1>
    <p class="error-message">
        Oops! Something went wrong on our end.
        Our team has been notified and we're working to fix it. Please try again later.
    </p>

    <div class="error-actions">
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Homepage</a>
        <a href="javascript:location.reload()" class="btn btn-secondary">Retry</a>
    </div>
@endsection