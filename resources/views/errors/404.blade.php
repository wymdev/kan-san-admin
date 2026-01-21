@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('custom-styles')
    <style>
        .search-icon {
            width: 140px;
            height: 140px;
            margin: 0 auto;
            position: relative;
        }

        .magnifying-circle {
            width: 100px;
            height: 100px;
            border: 8px solid #667eea;
            border-radius: 50%;
            position: absolute;
            top: 0;
            left: 0;
            animation: search 3s ease-in-out infinite;
        }

        .magnifying-handle {
            width: 8px;
            height: 60px;
            background: #764ba2;
            position: absolute;
            bottom: 0;
            right: 10px;
            transform: rotate(45deg);
            border-radius: 4px;
            transform-origin: top center;
        }

        .question-mark {
            font-size: 3.5rem;
            font-weight: 900;
            color: #764ba2;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes search {

            0%,
            100% {
                transform: translate(0, 0);
            }

            25% {
                transform: translate(10px, -10px);
            }

            50% {
                transform: translate(-10px, 10px);
            }

            75% {
                transform: translate(10px, 5px);
            }
        }

        @keyframes bounce {

            0%,
            100% {
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
        <div class="search-icon">
            <div class="magnifying-circle">
                <div class="question-mark">?</div>
            </div>
            <div class="magnifying-handle"></div>
        </div>
    </div>

    <h1 class="error-title">Page Not Found</h1>
    <p class="error-message">
        Oops! The page you're looking for seems to have wandered off.
        It might have been moved, deleted, or perhaps it never existed.
    </p>

    <div class="error-actions">
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
@endsection