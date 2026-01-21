@extends('errors.layout')

@section('title', '403 - Access Forbidden')

@section('custom-styles')
    <style>
        .lock-icon {
            width: 120px;
            height: 140px;
            margin: 0 auto;
            position: relative;
        }

        .lock-shackle {
            width: 70px;
            height: 70px;
            border: 10px solid #667eea;
            border-bottom: none;
            border-radius: 70px 70px 0 0;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            animation: shake 3s ease-in-out infinite;
        }

        .lock-body {
            width: 90px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lock-keyhole {
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            position: relative;
        }

        .lock-keyhole::after {
            content: '';
            width: 4px;
            height: 16px;
            background: white;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 0 0 2px 2px;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(-50%) rotate(0deg);
            }

            25% {
                transform: translateX(-50%) rotate(-3deg);
            }

            75% {
                transform: translateX(-50%) rotate(3deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="error-code">403</div>

    <div class="error-animation">
        <div class="lock-icon">
            <div class="lock-shackle"></div>
            <div class="lock-body">
                <div class="lock-keyhole"></div>
            </div>
        </div>
    </div>

    <h1 class="error-title">Access Forbidden</h1>
    <p class="error-message">
        Sorry, you don't have permission to access this resource.
        If you believe this is a mistake, please contact your administrator.
    </p>

    <div class="error-actions">
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
@endsection