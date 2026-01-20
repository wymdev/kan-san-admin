@extends('layouts.base', ['title' => 'Login'])

@section('css')
<style>
    * { box-sizing: border-box; }
    
    .login-container {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d1b4e 50%, #1a1a2e 100%);
        position: relative;
        overflow: hidden;
    }
    
    .login-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
        animation: rotate 30s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .login-card {
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        padding: 2.5rem 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        position: relative;
        z-index: 10;
    }
    
    .dark .login-card {
        background: rgba(17, 24, 39, 0.98);
    }
    
    @media (min-width: 640px) {
        .login-card {
            padding: 3rem 2.5rem;
        }
    }
    
    .logo-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }
    
    .logo-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .logo-text h1 {
        font-size: 1.25rem;
        font-weight: 800;
        color: white;
        letter-spacing: -0.025em;
        margin: 0;
    }
    
    .logo-text p {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    
    .form-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    
    .dark .form-title {
        color: #f9fafb;
    }
    
    .form-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 2rem;
    }
    
    .dark .form-subtitle {
        color: #9ca3af;
    }
    
    .input-group {
        margin-bottom: 1.25rem;
    }
    
    .input-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .dark .input-label {
        color: #d1d5db;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
        pointer-events: none;
        transition: color 0.2s;
    }
    
    .input-field {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        outline: none;
        transition: all 0.2s;
    }
    
    .dark .input-field {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .input-field:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .dark .input-field:focus {
        background: #111827;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
    }
    
    .input-field:focus + .input-icon,
    .input-wrapper:focus-within .input-icon {
        color: #6366f1;
    }
    
    .input-field::placeholder {
        color: #9ca3af;
    }
    
    .input-error {
        border-color: #ef4444 !important;
    }
    
    .error-text {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #ef4444;
    }
    
    .options-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    
    .checkbox-input {
        width: 1.125rem;
        height: 1.125rem;
        accent-color: #6366f1;
        cursor: pointer;
    }
    
    .checkbox-label {
        font-size: 0.875rem;
        color: #4b5563;
        cursor: pointer;
    }
    
    .dark .checkbox-label {
        color: #d1d5db;
    }
    
    .forgot-link {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6366f1;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .forgot-link:hover {
        color: #4f46e5;
    }
    
    .submit-btn {
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .submit-btn svg {
        width: 1.25rem;
        height: 1.25rem;
        transition: transform 0.2s;
    }
    
    .submit-btn:hover svg {
        transform: translateX(4px);
    }
    
    .footer-text {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
        font-size: 0.8125rem;
        color: #9ca3af;
    }
    
    .dark .footer-text {
        border-color: #374151;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-card">
        <div style="text-align: center;">
            <div class="logo-badge">
                <div class="logo-icon">
                    <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1>KAN SAN</h1>
                    <p>Lottery System</p>
                </div>
            </div>
            
            <h2 class="form-title">Welcome back</h2>
            <p class="form-subtitle">Sign in to your account to continue</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <label class="input-label" for="email">Email</label>
                <div class="input-wrapper">
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="input-field @error('email') input-error @enderror" 
                        placeholder="you@example.com"
                        required 
                        autocomplete="email" 
                        autofocus
                    >
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                @error('email')
                    <p class="error-text">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="input-group">
                <label class="input-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="input-field @error('password') input-error @enderror" 
                        placeholder="Enter your password"
                        required 
                        autocomplete="current-password"
                    >
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                @error('password')
                    <p class="error-text">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="options-row">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                    <span class="checkbox-label">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="submit-btn">
                Sign In
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </form>

        <p class="footer-text">&copy; {{ date('Y') }} Kan San. All rights reserved.</p>
    </div>
</div>
@endsection

@section('scripts')
@endsection
