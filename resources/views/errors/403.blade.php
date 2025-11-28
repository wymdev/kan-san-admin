@extends('errors.layout')

@section('title', '403 - Access Forbidden')

@section('custom-styles')
<style>
    .illustration-403 {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .lock-body {
        width: 100px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        animation: shake 3s ease-in-out infinite;
    }

    .lock-shackle {
        width: 60px;
        height: 60px;
        border: 12px solid #667eea;
        border-bottom: none;
        border-radius: 60px 60px 0 0;
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
    }

    .lock-keyhole {
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .lock-keyhole::after {
        content: '';
        width: 6px;
        height: 20px;
        background: white;
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 0 0 3px 3px;
    }

    @keyframes shake {
        0%, 100% {
            transform: translateX(-50%) rotate(0deg);
        }
        10%, 30%, 50%, 70%, 90% {
            transform: translateX(-50%) rotate(-5deg);
        }
        20%, 40%, 60%, 80% {
            transform: translateX(-50%) rotate(5deg);
        }
    }
</style>
@endsection

@section('content')
    <div class="error-code">403</div>
    
    <div class="error-animation">
        <div class="illustration-403">
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

    <div>
        <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
@endsection
