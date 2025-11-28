@extends('errors.layout')

@section('title', '419 - Too Many Requests')

@section('custom-styles')
<style>
    .illustration-419 {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .speedometer {
        width: 160px;
        height: 80px;
        border: 8px solid #667eea;
        border-bottom: none;
        border-radius: 160px 160px 0 0;
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
    }

    .speedometer-needle {
        width: 4px;
        height: 70px;
        background: #ef4444;
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform-origin: bottom center;
        transform: translateX(-50%) rotate(45deg);
        animation: swing 1.5s ease-in-out infinite;
        border-radius: 2px;
    }

    .speedometer-center {
        width: 20px;
        height: 20px;
        background: #667eea;
        border-radius: 50%;
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }

    .speed-lines {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .speed-line {
        width: 40px;
        height: 4px;
        background: #764ba2;
        position: absolute;
        border-radius: 2px;
        opacity: 0;
        animation: speedLine 1s ease-out infinite;
    }

    .speed-line:nth-child(1) {
        top: 20%;
        left: -20px;
        animation-delay: 0s;
    }

    .speed-line:nth-child(2) {
        top: 40%;
        left: -20px;
        animation-delay: 0.2s;
    }

    .speed-line:nth-child(3) {
        top: 60%;
        left: -20px;
        animation-delay: 0.4s;
    }

    @keyframes swing {
        0%, 100% {
            transform: translateX(-50%) rotate(30deg);
        }
        50% {
            transform: translateX(-50%) rotate(60deg);
        }
    }

    @keyframes speedLine {
        0% {
            left: -20px;
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            left: 220px;
            opacity: 0;
        }
    }
</style>
@endsection

@section('content')
    <div class="error-code">419</div>
    
    <div class="error-animation">
        <div class="illustration-419">
            <div class="speed-lines">
                <div class="speed-line"></div>
                <div class="speed-line"></div>
                <div class="speed-line"></div>
            </div>
            <div class="speedometer"></div>
            <div class="speedometer-needle"></div>
            <div class="speedometer-center"></div>
        </div>
    </div>

    <h1 class="error-title">Too Many Requests</h1>
    <p class="error-message">
        Whoa there! You're going too fast. 
        Please slow down and try again in a few moments. We've temporarily limited your requests to keep things running smoothly.
    </p>

    <div>
        <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
@endsection
