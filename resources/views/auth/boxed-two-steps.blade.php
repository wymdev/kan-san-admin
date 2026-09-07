@extends('layouts.base', ['title' => 'Verify OTP'])

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
           OTP step. Shares the sign-in palette and, like it, is keyed on
           [data-theme] — the attribute the app actually sets. The old
           `.dark` selectors here never matched.
           ============================================================ */
        :root {
            --auth-canvas: #f4f5f7;
            --auth-card: #ffffff;
            --auth-field: #f8fafc;
            --auth-field-focus: #ffffff;
            --auth-line: #e4e7ec;
            --auth-line-strong: #cbd2dc;
            --auth-text: #101828;
            --auth-muted: #667085;
            --auth-dim: #98a2b3;
            --auth-accent: #4f46e5;
            --auth-accent-hover: #4338ca;
            --auth-accent-ring: rgba(79, 70, 229, .14);
            --auth-accent-soft: rgba(79, 70, 229, .07);
            --auth-danger: #d92d20;
            --auth-success: #067647;
            --auth-panel-a: #1e1b4b;
            --auth-panel-b: #312e81;
        }

        [data-theme="dark"] {
            --auth-canvas: #09090b;
            --auth-card: #131316;
            --auth-field: #1a1a1f;
            --auth-field-focus: #0d0d10;
            --auth-line: #27272a;
            --auth-line-strong: #3f3f46;
            --auth-text: #fafafa;
            --auth-muted: #a1a1aa;
            --auth-dim: #71717a;
            --auth-accent: #818cf8;
            --auth-accent-hover: #a5b4fc;
            --auth-accent-ring: rgba(129, 140, 248, .18);
            --auth-accent-soft: rgba(129, 140, 248, .1);
            --auth-danger: #f97066;
            --auth-success: #47cd89;
            --auth-panel-a: #16162b;
            --auth-panel-b: #1e1b4b;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--auth-canvas);
            font-family: 'Outfit', 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--auth-text);
            -webkit-font-smoothing: antialiased;
        }

        .otp-wrap {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background:
                radial-gradient(36rem 22rem at 50% -6rem, var(--auth-accent-soft), transparent 70%);
        }

        .otp-card {
            width: 100%;
            max-width: 27rem;
            background: var(--auth-card);
            border: 1px solid var(--auth-line);
            border-radius: 18px;
            padding: 2.25rem 1.75rem;
            box-shadow: 0 20px 45px -25px rgba(16, 24, 40, .35);
        }

        @media (min-width: 640px) {
            .otp-card {
                padding: 2.5rem 2.25rem;
            }
        }

        .otp-mark {
            width: 52px;
            height: 52px;
            margin: 0 auto 1.25rem;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--auth-accent);
            color: #fff;
        }

        .otp-title {
            margin: 0 0 .5rem;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.03em;
            text-align: center;
        }

        .otp-sub {
            margin: 0 0 1.75rem;
            font-size: .9375rem;
            color: var(--auth-muted);
            text-align: center;
            line-height: 1.6;
        }

        .otp-sub b {
            color: var(--auth-text);
            font-weight: 600;
            /* Long addresses must not blow out the card. */
            overflow-wrap: anywhere;
        }

        .alert {
            display: flex;
            gap: .625rem;
            padding: .75rem .875rem;
            margin-bottom: 1.25rem;
            border-radius: 10px;
            font-size: .875rem;
        }

        .alert svg {
            flex: none;
            margin-top: .1rem;
        }

        .alert-error {
            background: color-mix(in srgb, var(--auth-danger) 10%, transparent);
            border: 1px solid color-mix(in srgb, var(--auth-danger) 32%, transparent);
            color: var(--auth-danger);
        }

        .alert-ok {
            background: color-mix(in srgb, var(--auth-success) 10%, transparent);
            border: 1px solid color-mix(in srgb, var(--auth-success) 32%, transparent);
            color: var(--auth-success);
        }

        .code-label {
            display: block;
            margin-bottom: .625rem;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--auth-muted);
            text-align: center;
        }

        .code-row {
            display: flex;
            justify-content: center;
            gap: .625rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .code-row {
                gap: .875rem;
            }
        }

        .code-input {
            width: 3.25rem;
            height: 3.75rem;
            font: inherit;
            font-size: 1.625rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: center;
            color: var(--auth-text);
            background: var(--auth-field);
            border: 1.5px solid var(--auth-line);
            border-radius: 12px;
            outline: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }

        @media (max-width: 380px) {
            .code-input {
                width: 3rem;
                height: 3.5rem;
                font-size: 1.5rem;
            }
        }

        .code-input:focus {
            border-color: var(--auth-accent);
            background: var(--auth-field-focus);
            box-shadow: 0 0 0 4px var(--auth-accent-ring);
        }

        .code-input.is-filled {
            border-color: var(--auth-accent);
            background: var(--auth-accent-soft);
        }

        .code-input.is-error {
            border-color: var(--auth-danger);
        }

        .submit {
            width: 100%;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: var(--auth-accent);
            color: #fff;
            border: 0;
            border-radius: 10px;
            font: inherit;
            font-size: .9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .submit:hover:not(:disabled) {
            background: var(--auth-accent-hover);
        }

        .submit:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .submit:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px var(--auth-accent-ring);
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .resend {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--auth-line);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            flex-wrap: wrap;
            font-size: .875rem;
            color: var(--auth-muted);
        }

        .resend form {
            display: inline;
        }

        .resend button {
            font: inherit;
            font-weight: 600;
            color: var(--auth-accent);
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
        }

        .resend button:hover {
            color: var(--auth-accent-hover);
            text-decoration: underline;
        }

        .otp-foot {
            margin: 1.5rem 0 0;
            text-align: center;
            font-size: .75rem;
            color: var(--auth-dim);
        }

        .otp-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            margin-top: .75rem;
            font-size: .8125rem;
        }

        .otp-back button {
            font: inherit;
            color: var(--auth-dim);
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
            text-decoration: underline;
        }

        .otp-back button:hover {
            color: var(--auth-muted);
        }
    </style>
@endsection

@section('content')
    <div class="otp-wrap">
        <div class="otp-card">
            <div class="otp-mark"><x-icon name="shield-check" :size="26" /></div>

            <h1 class="otp-title">Check your email</h1>
            <p class="otp-sub">
                We sent a 4-digit code to<br>
                <b>{{ auth()->user()->email ?? 'your email address' }}</b>
            </p>

            @if (session('message'))
                <div class="alert alert-ok" role="status">
                    <x-icon name="check-circle" :size="16" />
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <x-icon name="alert-circle" :size="16" />
                    <div>
                        @foreach ($errors->all() as $error)
                            <p style="margin:0">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('verify.otp') }}" id="otpForm">
                @csrf
                <input type="hidden" name="otp" id="otpValue">

                <label class="code-label" for="code-0">Enter your verification code</label>

                <div class="code-row">
                    @for ($i = 0; $i < 4; $i++)
                        <input id="code-{{ $i }}" class="code-input @error('otp') is-error @enderror" type="text"
                            maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                            aria-label="Digit {{ $i + 1 }} of 4">
                    @endfor
                </div>

                <button type="submit" class="submit" id="verifyBtn">
                    <span>Verify code</span>
                    <x-icon name="arrow-right" :size="17" />
                </button>
            </form>

            <div class="resend">
                <span>Didn't get it?</span>
                <form method="POST" action="{{ route('resend.otp') }}">
                    @csrf
                    <button type="submit">Send a new code</button>
                </form>
            </div>

            <div class="otp-back">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign in with a different account</button>
                </form>
            </div>

            <p class="otp-foot">The code expires in 10 minutes.</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // head-css yields this section inside <head>, so wait for the document.
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = Array.from(document.querySelectorAll('.code-input'));
            const form = document.getElementById('otpForm');
            const hidden = document.getElementById('otpValue');
            const btn = document.getElementById('verifyBtn');
            if (!inputs.length || !form) return;

            let submitting = false;

            const code = () => inputs.map((i) => i.value).join('');

            function sync() {
                hidden.value = code();
                inputs.forEach((i) => i.classList.toggle('is-filled', i.value !== ''));
            }

            function submitWhenComplete() {
                if (submitting || code().length !== 4) return;
                submitting = true;
                btn.disabled = true;
                btn.querySelector('span').textContent = 'Verifying…';
                btn.querySelector('svg').classList.add('spin');
                form.submit();
            }

            inputs.forEach((input, index) => {
                input.addEventListener('input', () => {
                    // A phone keyboard can deliver several digits at once.
                    const digits = input.value.replace(/\D/g, '');
                    if (digits.length > 1) {
                        digits.split('').forEach((d, k) => {
                            if (inputs[index + k]) inputs[index + k].value = d;
                        });
                        const last = Math.min(index + digits.length, inputs.length - 1);
                        inputs[last].focus();
                    } else {
                        input.value = digits;
                        if (digits && index < inputs.length - 1) inputs[index + 1].focus();
                    }
                    sync();
                    submitWhenComplete();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        e.preventDefault();
                        inputs[index - 1].value = '';
                        inputs[index - 1].focus();
                        sync();
                    }
                    if (e.key === 'ArrowLeft' && index > 0) inputs[index - 1].focus();
                    if (e.key === 'ArrowRight' && index < inputs.length - 1) inputs[index + 1].focus();
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    const digits = text.replace(/\D/g, '').slice(0, inputs.length).split('');
                    digits.forEach((d, k) => { inputs[k].value = d; });
                    inputs[Math.min(digits.length, inputs.length - 1)].focus();
                    sync();
                    submitWhenComplete();
                });

                input.addEventListener('focus', () => input.select());
            });

            // Guard the manual click too, so a complete code is always sent.
            form.addEventListener('submit', (e) => {
                sync();
                if (code().length !== 4) {
                    e.preventDefault();
                    inputs[Math.max(0, code().length)].focus();
                }
            });

            inputs[0].focus();
        });
    </script>
@endsection
