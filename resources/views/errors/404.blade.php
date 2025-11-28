@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('custom-styles')
<style>
    .illustration-404 {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .magnifying-glass {
        width: 120px;
        height: 120px;
        border: 8px solid #667eea;
        border-radius: 50%;
        position: absolute;
        top: 20px;
        left: 20px;
        animation: search 3s ease-in-out infinite;
    }

    .magnifying-handle {
        width: 8px;
        height: 80px;
        background: #667eea;
        position: absolute;
        bottom: -40px;
        right: 10px;
        transform: rotate(45deg);
        border-radius: 4px;
    }

    .question-mark {
        font-size: 60px;
        color: #764ba2;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes search {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        25% {
            transform: translate(10px, -10px) rotate(5deg);
        }
        50% {
            transform: translate(-10px, 10px) rotate(-5deg);
        }
        75% {
            transform: translate(10px, 10px) rotate(5deg);
        }
    }

    @keyframes bounce {
        0%, 100% {
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.1);
        }
    }
</style>
@endsection

@section('content')
    <div class="error-code">404</div>
    
    <div class="error-animation">
        <div class="illustration-404">
            <div class="magnifying-glass">
                <div class="magnifying-handle"></div>
            </div>
            <div class="question-mark">?</div>
        </div>
    </div>

    <h1 class="error-title">Page Not Found</h1>
    <p class="error-message">
        Oops! The page you're looking for seems to have wandered off. 
        It might have been moved, deleted, or perhaps it never existed.
    </p>

    <div>
        <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
@endsection
