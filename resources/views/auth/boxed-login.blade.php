@extends('layouts.base', ['title' => 'Login'])

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
           Sign-in. Theme-aware via [data-theme] — the attribute the app
           actually sets. The previous `.dark` class selectors never
           matched, so dark mode did nothing here.
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
            --auth-danger: #d92d20;
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
            --auth-danger: #f97066;
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

        .auth-wrap {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 1024px) {
            .auth-wrap {
                grid-template-columns: 1.05fr 1fr;
            }
        }

        /* ---------- Brand panel (desktop only) ---------- */
        .auth-aside {
            display: none;
            position: relative;
            overflow: hidden;
            padding: 3.5rem;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(150deg, var(--auth-panel-a), var(--auth-panel-b));
            color: #e9e9ff;
        }

        @media (min-width: 1024px) {
            .auth-aside {
                display: flex;
            }
        }

        .auth-aside::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(38rem 22rem at 15% 0%, rgba(129, 140, 248, .3), transparent 65%),
                radial-gradient(30rem 22rem at 100% 100%, rgba(236, 72, 153, .18), transparent 65%);
            pointer-events: none;
        }

        .auth-aside>* {
            position: relative;
            z-index: 1;
        }

        .aside-brand {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            font-weight: 700;
        }

        .aside-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .18);
        }

        .aside-brand .n {
            font-size: 1.0625rem;
            letter-spacing: -.02em;
        }

        .aside-brand .s {
            display: block;
            font-size: .625rem;
            font-weight: 600;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: rgba(233, 233, 255, .6);
        }

        .aside-copy h2 {
            margin: 0 0 .75rem;
            font-size: clamp(1.75rem, 2.4vw, 2.375rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -.03em;
        }

        .aside-copy p {
            margin: 0;
            max-width: 30rem;
            color: rgba(233, 233, 255, .72);
            line-height: 1.6;
        }

        .aside-points {
            list-style: none;
            margin: 2rem 0 0;
            padding: 0;
            display: grid;
            gap: .75rem;
        }

        .aside-points li {
            display: flex;
            align-items: center;
            gap: .625rem;
            font-size: .9375rem;
            color: rgba(233, 233, 255, .82);
        }

        .aside-points svg {
            flex: none;
            color: #a5b4fc;
        }

        .aside-foot {
            font-size: .8125rem;
            color: rgba(233, 233, 255, .5);
        }

        /* ---------- Form side ---------- */
        .auth-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 25rem;
        }

        .card-brand {
            display: inline-flex;
            align-items: center;
            gap: .625rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 1024px) {
            .card-brand {
                display: none;
            }
        }

        .card-brand .mark {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--auth-accent);
            color: #fff;
        }

        .card-brand .n {
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .card-brand .s {
            display: block;
            font-size: .625rem;
            font-weight: 600;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--auth-dim);
        }

        .auth-title {
            margin: 0 0 .375rem;
            font-size: 1.625rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .auth-sub {
            margin: 0 0 1.75rem;
            color: var(--auth-muted);
            font-size: .9375rem;
        }

        .alert {
            display: flex;
            gap: .625rem;
            padding: .75rem .875rem;
            margin-bottom: 1.25rem;
            border-radius: 10px;
            background: color-mix(in srgb, var(--auth-danger) 10%, transparent);
            border: 1px solid color-mix(in srgb, var(--auth-danger) 32%, transparent);
            color: var(--auth-danger);
            font-size: .875rem;
        }

        .alert svg {
            flex: none;
            margin-top: .1rem;
        }

        .field {
            margin-bottom: 1.125rem;
        }

        .field label {
            display: block;
            margin-bottom: .4375rem;
            font-size: .875rem;
            font-weight: 600;
            color: var(--auth-text);
        }

        .control {
            position: relative;
            display: flex;
            align-items: center;
        }

        .control>svg:first-child {
            position: absolute;
            left: .875rem;
            color: var(--auth-dim);
            pointer-events: none;
            transition: color .15s;
        }

        .control:focus-within>svg:first-child {
            color: var(--auth-accent);
        }

        .input {
            width: 100%;
            height: 46px;
            padding: 0 .875rem 0 2.75rem;
            background: var(--auth-field);
            border: 1.5px solid var(--auth-line);
            border-radius: 10px;
            color: var(--auth-text);
            font: inherit;
            font-size: .9375rem;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }

        .input::placeholder {
            color: var(--auth-dim);
        }

        .input:focus {
            outline: none;
            background: var(--auth-field-focus);
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 4px var(--auth-accent-ring);
        }

        .input.has-error {
            border-color: var(--auth-danger);
        }

        .pw-toggle {
            position: absolute;
            right: .5rem;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: none;
            border: 0;
            border-radius: 8px;
            color: var(--auth-dim);
            cursor: pointer;
        }

        .pw-toggle:hover {
            color: var(--auth-text);
        }

        .input[type="password"],
        .control:has(.pw-toggle) .input {
            padding-right: 2.75rem;
        }

        .field-error {
            display: flex;
            align-items: center;
            gap: .375rem;
            margin: .4375rem 0 0;
            font-size: .8125rem;
            color: var(--auth-danger);
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            user-select: none;
            font-size: .875rem;
            color: var(--auth-muted);
        }

        .check input {
            appearance: none;
            width: 18px;
            height: 18px;
            flex: none;
            border: 1.5px solid var(--auth-line-strong);
            border-radius: 5px;
            background: var(--auth-field);
            cursor: pointer;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 11px;
            transition: background-color .15s, border-color .15s;
        }

        .check input:checked {
            background-color: var(--auth-accent);
            border-color: var(--auth-accent);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
        }

        .check input:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px var(--auth-accent-ring);
        }

        .link {
            font-size: .875rem;
            font-weight: 600;
            color: var(--auth-accent);
            text-decoration: none;
        }

        .link:hover {
            color: var(--auth-accent-hover);
            text-decoration: underline;
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

        .submit:hover {
            background: var(--auth-accent-hover);
        }

        .submit:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px var(--auth-accent-ring);
        }

        .auth-foot {
            margin: 1.75rem 0 0;
            padding-top: 1.25rem;
            border-top: 1px solid var(--auth-line);
            text-align: center;
            font-size: .8125rem;
            color: var(--auth-dim);
        }

        .auth-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            margin-top: .5rem;
            font-size: .75rem;
            color: var(--auth-dim);
        }
    </style>
@endsection

@section('content')
    <div class="auth-wrap">
        {{-- ---------- Brand panel ---------- --}}
        <aside class="auth-aside">
            <div class="aside-brand">
                <span class="aside-mark"><x-icon name="crown" :size="21" /></span>
                <span>
                    <span class="n">Kan San</span>
                    <span class="s">Admin Console</span>
                </span>
            </div>

            <div class="aside-copy">
                <h2>Run the whole lottery operation from one place.</h2>
                <p>Draw results, ticket sales, customers and payouts — synced with the Government Lottery Office and kept
                    in one audited system.</p>
                <ul class="aside-points">
                    <li><x-icon name="shield-check" :size="18" /> Two-step verification on every sign-in</li>
                    <li><x-icon name="trophy" :size="18" /> Official draw results synced automatically</li>
                    <li><x-icon name="ticket" :size="18" /> Primary and secondary sales in one ledger</li>
                </ul>
            </div>

            <p class="aside-foot">&copy; {{ date('Y') }} Kan San Application</p>
        </aside>

        {{-- ---------- Sign-in form ---------- --}}
        <main class="auth-main">
            <div class="auth-card">
                <div class="card-brand">
                    <span class="mark"><x-icon name="crown" :size="19" /></span>
                    <span>
                        <span class="n">Kan San</span>
                        <span class="s">Admin Console</span>
                    </span>
                </div>

                <h1 class="auth-title">Sign in</h1>
                <p class="auth-sub">Enter your credentials to continue to the console.</p>

                @if (session('error'))
                    <div class="alert" role="alert">
                        <x-icon name="alert-circle" :size="16" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="control">
                            <x-icon name="mail" :size="18" />
                            <input id="email" class="input @error('email') has-error @enderror" type="email" name="email"
                                value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email"
                                autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <p class="field-error" id="email-error">
                                <x-icon name="alert-circle" :size="13" /> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="control">
                            <x-icon name="lock" :size="18" />
                            <input id="password" class="input @error('password') has-error @enderror" type="password"
                                name="password" placeholder="••••••••" required autocomplete="current-password"
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password"
                                aria-pressed="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error" id="password-error">
                                <x-icon name="alert-circle" :size="13" /> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="row">
                        <label class="check">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="submit">
                        Sign in
                        <x-icon name="arrow-right" :size="17" />
                    </button>
                </form>

                <p class="auth-note">
                    <x-icon name="shield-check" :size="13" />
                    A one-time code will be sent to your email after this step.
                </p>

                <p class="auth-foot">&copy; {{ date('Y') }} Kan San. All rights reserved.</p>
            </div>
        </main>
    </div>
@endsection

@section('scripts')
    <script>
        // layouts/partials/head-css yields this section inside <head>, so wait
        // for the document before reaching for form elements.
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('pwToggle');
            const field = document.getElementById('password');
            if (!toggle || !field) return;

            toggle.addEventListener('click', function () {
                const revealed = field.type === 'text';
                field.type = revealed ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!revealed));
                toggle.setAttribute('aria-label', revealed ? 'Show password' : 'Hide password');
                field.focus();
            });
        });
    </script>
@endsection
