@extends('errors.layout')

@section('title', '403 - Access Forbidden')

@section('custom-styles')
    <style>
        .icon-svg {
            width: 60px;
            height: 60px;
        }
    </style>
@endsection

@section('content')
    <div class="error-code">403</div>

    <div class="icon-wrapper">
        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="11" width="14" height="10" rx="2" stroke="url(#grad1)" stroke-width="2" />
            <path d="M8 11V7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7V11" stroke="url(#grad1)" stroke-width="2"
                stroke-linecap="round" />
            <circle cx="12" cy="16" r="1.5" fill="url(#grad1)" />
            <defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#ec4899;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <h1>Access Forbidden</h1>
    <p>You don't have permission to access this resource. If you believe this is an error, please contact your
        administrator.</p>

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