@extends('layouts.base', ['title' => 'Verify OTP'])

@section('css')
<style>
    * { box-sizing: border-box; }
    
    .otp-container {
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
    
    .otp-container::before {
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
    
    .otp-card {
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        padding: 2.5rem 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        position: relative;
        z-index: 10;
    }
    
    .dark .otp-card {
        background: rgba(17, 24, 39, 0.98);
    }
    
    @media (min-width: 640px) {
        .otp-card {
            padding: 3rem 2.5rem;
        }
    }
    
    .otp-icon {
        width: 5rem;
        height: 5rem;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
    }
    
    .otp-icon svg {
        width: 2.5rem;
        height: 2.5rem;
        color: white;
    }
    
    .otp-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    .dark .otp-title {
        color: #f9fafb;
    }
    
    .otp-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 2rem;
        text-align: center;
        line-height: 1.6;
    }
    
    .dark .otp-subtitle {
        color: #9ca3af;
    }
    
    .otp-subtitle strong {
        color: #6366f1;
    }
    
    .otp-inputs {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    @media (min-width: 640px) {
        .otp-inputs {
            gap: 1rem;
        }
    }
    
    .otp-input {
        width: 3.5rem;
        height: 3.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        color: #111827;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        outline: none;
        transition: all 0.2s;
    }
    
    @media (min-width: 640px) {
        .otp-input {
            width: 4rem;
            height: 4rem;
            font-size: 1.75rem;
        }
    }
    
    .dark .otp-input {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .otp-input:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .dark .otp-input:focus {
        background: #111827;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
    }
    
    .otp-input.filled {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }
    
    .error-box {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .dark .error-box {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
    }
    
    .error-box svg {
        flex-shrink: 0;
        width: 1.25rem;
        height: 1.25rem;
        color: #ef4444;
        margin-top: 0.125rem;
    }
    
    .error-box p {
        font-size: 0.875rem;
        color: #dc2626;
        margin: 0;
    }
    
    .dark .error-box p {
        color: #fca5a5;
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
        margin-bottom: 1rem;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    
    .submit-btn svg {
        width: 1.25rem;
        height: 1.25rem;
        transition: transform 0.2s;
    }
    
    .submit-btn:hover svg {
        transform: translateX(4px);
    }
    
    .resend-section {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .dark .resend-section {
        border-color: #374151;
    }
    
    .resend-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }
    
    .dark .resend-text {
        color: #9ca3af;
    }
    
    .resend-btn {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6366f1;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        transition: color 0.2s;
    }
    
    .resend-btn:hover {
        color: #4f46e5;
        text-decoration: underline;
    }
    
    .resend-btn:disabled {
        color: #9ca3af;
        cursor: not-allowed;
    }
    
    .footer-text {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.8125rem;
        color: #9ca3af;
    }
</style>
@endsection

@section('content')
<div class="otp-container">
    <div class="otp-card">
        <div class="otp-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        
        <h2 class="otp-title">Verify Your Email</h2>
        <p class="otp-subtitle">
            We've sent a <strong>4-digit</strong> code to<br>
            <strong>{{ auth()->user()->email ?? 'your email' }}</strong>
        </p>

        @if ($errors->any())
            <div class="error-box">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('verify.otp') }}" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="otpHidden">
            
            <div class="otp-inputs">
                <input type="text" maxlength="1" class="otp-input" data-index="0" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                <input type="text" maxlength="1" class="otp-input" data-index="1" inputmode="numeric" pattern="[0-9]*">
                <input type="text" maxlength="1" class="otp-input" data-index="2" inputmode="numeric" pattern="[0-9]*">
                <input type="text" maxlength="1" class="otp-input" data-index="3" inputmode="numeric" pattern="[0-9]*">
            </div>

            <button type="submit" class="submit-btn" id="verifyBtn">
                Verify Code
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </form>

        <div class="resend-section">
            <p class="resend-text">Didn't receive the code?</p>
            <form method="POST" action="{{ route('resend.otp') }}" style="display: inline;">
                @csrf
                <button type="submit" class="resend-btn">Resend Code</button>
            </form>
        </div>

        <p class="footer-text">&copy; {{ date('Y') }} Kan San. All rights reserved.</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const hiddenInput = document.getElementById('otpHidden');
    const verifyBtn = document.getElementById('verifyBtn');
    
    // Focus first input
    inputs[0].focus();
    
    inputs.forEach((input, index) => {
        // Handle input
        input.addEventListener('input', function(e) {
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            
            if (value) {
                e.target.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else {
                e.target.classList.remove('filled');
            }
            
            updateHiddenInput();
            checkAutoSubmit();
        });
        
        // Handle keydown for backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
            }
        });
        
        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').split('').slice(0, 4);
            
            digits.forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                    inputs[i].classList.add('filled');
                }
            });
            
            if (digits.length > 0) {
                const focusIndex = Math.min(digits.length, inputs.length - 1);
                inputs[focusIndex].focus();
            }
            
            updateHiddenInput();
            checkAutoSubmit();
        });
    });
    
    function updateHiddenInput() {
        hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
    }
    
    function checkAutoSubmit() {
        const otp = Array.from(inputs).map(i => i.value).join('');
        if (otp.length === 4) {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = 'Verifying... <svg class="animate-spin" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
            setTimeout(() => form.submit(), 500);
        }
    }
});
</script>
@endsection
