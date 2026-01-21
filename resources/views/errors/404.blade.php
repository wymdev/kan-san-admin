@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('custom-styles')
    <style>
        .icon-svg {
            width: 60px;
            height: 60px;
        }
    </style>
@endsection

@section('content')
    <div class="error-code">404</div>

    <div class="icon-wrapper">
        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="11" cy="11" r="8" stroke="url(#grad2)" stroke-width="2" />
            <path d="M21 21L16.65 16.65" stroke="url(#grad2)" stroke-width="2" stroke-linecap="round" />
            <path d="M11 8V11L13 13" stroke="url(#grad2)" stroke-width="2" stroke-linecap="round" />
            <defs>
                <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <h1>Page Not Found</h1>
    <p>The page you're looking for doesn't exist. It might have been moved or deleted, or the URL might be incorrect.</p>

    <div class="actions">
        <a href="{{ url('/') }}" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            </svg>
            Go Home
        </a>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Go Back
        </a>
    </div>
@endsection