@extends('errors.layout')

@section('title', '500 - Server Error')

@section('custom-styles')
<style>
    .illustration-500 {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .server {
        width: 120px;
        height: 140px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        animation: glitch 2s ease-in-out infinite;
    }

    .server-light {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: absolute;
        left: 15px;
    }

    .server-light:nth-child(1) {
        top: 20px;
        background: #ef4444;
        animation: blink 1s ease-in-out infinite;
    }

    .server-light:nth-child(2) {
        top: 40px;
        background: #ef4444;
        animation: blink 1s ease-in-out infinite 0.2s;
    }

    .server-light:nth-child(3) {
        top: 60px;
        background: #f59e0b;
        animation: blink 1s ease-in-out infinite 0.4s;
    }

    .crack {
        width: 3px;
        height: 80px;
        background: rgba(255, 255, 255, 0.3);
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%) rotate(15deg);
        animation: crackGrow 3s ease-out infinite;
    }

    .crack::before,
    .crack::after {
        content: '';
        position: absolute;
        width: 3px;
        background: rgba(255, 255, 255, 0.3);
    }

    .crack::before {
        height: 30px;
        top: 20px;
        left: -10px;
        transform: rotate(-30deg);
    }

    .crack::after {
        height: 25px;
        top: 40px;
        right: -12px;
        transform: rotate(30deg);
    }

    .sparks {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .spark {
        width: 4px;
        height: 4px;
        background: #fbbf24;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        animation: sparkle 2s ease-out infinite;
    }

    .spark:nth-child(1) {
        animation-delay: 0s;
    }

    .spark:nth-child(2) {
        animation-delay: 0.5s;
    }

    .spark:nth-child(3) {
        animation-delay: 1s;
    }

    @keyframes glitch {
        0%, 90%, 100% {
            transform: translateX(-50%);
        }
        92%, 96% {
            transform: translateX(-48%) skew(2deg);
        }
        94%, 98% {
            transform: translateX(-52%) skew(-2deg);
        }
    }

    @keyframes blink {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 0 10px currentColor;
        }
        50% {
            opacity: 0.3;
            box-shadow: none;
        }
    }

    @keyframes crackGrow {
        0% {
            height: 0;
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            height: 80px;
            opacity: 0.8;
        }
    }

    @keyframes sparkle {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(var(--tx), var(--ty)) scale(0);
            opacity: 0;
        }
    }

    .spark:nth-child(1) {
        --tx: -30px;
        --ty: -30px;
    }

    .spark:nth-child(2) {
        --tx: 30px;
        --ty: -20px;
    }

    .spark:nth-child(3) {
        --tx: -20px;
        --ty: 30px;
    }
</style>
@endsection

@section('content')
    <div class="error-code">500</div>
    
    <div class="error-animation">
        <div class="illustration-500">
            <div class="sparks">
                <div class="spark"></div>
                <div class="spark"></div>
                <div class="spark"></div>
            </div>
            <div class="server">
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

    <div>
        <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
        <a href="javascript:location.reload()" class="btn btn-secondary">Retry</a>
    </div>
@endsection
