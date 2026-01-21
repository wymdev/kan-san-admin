@extends('errors.layout')

@section('title', '419 - Page Expired')

@section('custom-styles')
    <style>
        .timer-icon {
            width: 140px;
            height: 140px;
            margin: 0 auto;
            position: relative;
        }

        .clock-circle {
            width: 120px;
            height: 120px;
            border: 8px solid #667eea;
            border-radius: 50%;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        .clock-hand {
            width: 4px;
            height: 40px;
            background: #764ba2;
            position: absolute;
            bottom: 50%;
            left: 50%;
            transform-origin: bottom center;
            transform: translateX(-50%) rotate(0deg);
            border-radius: 2px;
            animation: rotate 4s linear infinite;
        }

        .clock-center {
            width: 12px;
            height: 12px;
            background: #764ba2;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        @keyframes rotate {
            from {
                transform: translateX(-50%) rotate(0deg);
            }

            to {
                transform: translateX(-50%) rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="error-code">419</div>

    <div class="error-animation">
        <div class="timer-icon">
            <div class="clock-circle">
                <div class="clock-hand"></div>
                <div class="clock-center"></div>
            </div>
        </div>
    </div>

    <h1 class="error-title">Page Expired</h1>
    <p class="error-message">
        Your session has expired due to inactivity.
        Please refresh the page and try again.
    </p>

    <div class="error-actions">
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Homepage</a>
        <a href="javascript:location.reload()" class="btn btn-secondary">Refresh Page</a>
    </div>
@endsection