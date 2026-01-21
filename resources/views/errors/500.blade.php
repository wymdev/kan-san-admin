@extends('errors.layout')

@section('title', '500 - Server Error')

@section('custom-styles')
    <style>
        .icon-svg {
            width: 60px;
            height: 60px;
        }
    </style>
@endsection

@section('content')
    <div class="error-code">500</div>

    <div class="icon-wrapper">
        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9V12L14 14" stroke="url(#grad3)" stroke-width="2" stroke-linecap="round" />
            <circle cx="12" cy="5" r="1.5" fill="url(#grad3)" />
            <path
                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                stroke="url(#grad3)" stroke-width="2" />
            <defs>
                <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#ec4899;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <h1>Server Error</h1>
    <p>Something went wrong on our end. We're working to fix the issue. Please try again in a few moments.</p>

    <div class="actions">
        <a href="{{ url('/') }}" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            </svg>
            Go Home
        </a>
        <a href="javascript:location.reload()" class="btn btn-secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 4v6h6M23 20v-6h-6" />
                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" />
            </svg>
            Retry
        </a>
    </div>
@endsection