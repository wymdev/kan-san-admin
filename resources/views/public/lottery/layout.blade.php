<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Official Thai Government Lottery results and ticket checking.')">
    <title>@yield('title', 'Lottery Results') · Kan San</title>
    <link rel="shortcut icon" href="/images/logo-bg.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ============================================================
           Design tokens — the single source of truth for every public
           lottery page. Previously each page carried its own palette.
           ============================================================ */
        :root {
            --bg: #0a0f1c;
            --bg-elevated: #0e1626;
            --surface: #141d30;
            --surface-2: #1c2740;
            --line: rgba(255, 255, 255, .08);
            --line-strong: rgba(255, 255, 255, .16);

            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dim: #64748b;

            --gold: #fbbf24;
            --gold-soft: rgba(251, 191, 36, .14);
            --gold-line: rgba(251, 191, 36, .35);
            --ink-on-gold: #2a1a00;

            --green: #34d399;
            --green-soft: rgba(52, 211, 153, .12);
            --green-line: rgba(52, 211, 153, .3);
            --blue: #60a5fa;
            --blue-soft: rgba(96, 165, 250, .12);
            --blue-line: rgba(96, 165, 250, .3);
            --red: #f87171;

            --r-sm: 10px;
            --r-md: 14px;
            --r-lg: 20px;

            --pad: clamp(1rem, 3vw, 1.5rem);
            --shadow: 0 18px 40px -18px rgba(0, 0, 0, .8);

            color-scheme: dark;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            color: var(--text);
            font-family: 'Outfit', 'Noto Sans Thai', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 15px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        /* A single soft light source rather than the animated blobs and
           particle loops each page used to run. */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(60rem 30rem at 50% -12rem, rgba(251, 191, 36, .10), transparent 70%),
                radial-gradient(50rem 30rem at 100% 100%, rgba(96, 165, 250, .07), transparent 70%);
        }

        h1,
        h2,
        h3,
        h4 {
            margin: 0;
            line-height: 1.2;
            letter-spacing: -.02em;
            font-weight: 700;
        }

        p {
            margin: 0;
        }

        a {
            color: inherit;
        }

        img,
        svg {
            display: block;
        }

        :where(a, button, input, [tabindex]):focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
            border-radius: 6px;
        }

        /* ---------- Shell ---------- */
        .shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 var(--pad);
        }

        main.shell {
            flex: 1;
            padding-top: clamp(1.5rem, 4vw, 2.5rem);
            padding-bottom: clamp(2rem, 5vw, 3.5rem);
        }

        /* ---------- Nav ---------- */
        .nav {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(10, 15, 28, .82);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--line);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 60px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .625rem;
            text-decoration: none;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: var(--r-sm);
            background: linear-gradient(150deg, var(--gold), #f59e0b);
            color: var(--ink-on-gold);
            flex: none;
        }

        .brand-name {
            font-size: 1rem;
        }

        .brand-sub {
            display: block;
            font-size: .625rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--text-dim);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .75rem;
            border-radius: var(--r-sm);
            font-size: .875rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: background .15s, color .15s;
        }

        .nav-link:hover {
            background: var(--surface);
            color: var(--text);
        }

        .nav-link[aria-current="page"] {
            background: var(--gold-soft);
            color: var(--gold);
        }

        /* ---------- Page header ---------- */
        .page-head {
            margin-bottom: clamp(1.25rem, 3vw, 2rem);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: .5rem;
        }

        .page-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 800;
        }

        .page-sub {
            margin-top: .375rem;
            color: var(--text-muted);
            font-size: .9375rem;
        }

        /* ---------- Card ---------- */
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            overflow: hidden;
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .875rem var(--pad);
            border-bottom: 1px solid var(--line);
            background: var(--bg-elevated);
        }

        .card-head h2 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .9375rem;
            font-weight: 600;
        }

        .card-head h2 svg {
            color: var(--gold);
            flex: none;
        }

        .card-body {
            padding: var(--pad);
        }

        /* ---------- Numbers ----------
           Tabular figures keep every digit on the same grid, which is what
           makes a column of lottery numbers scannable. */
        .num {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
            letter-spacing: .12em;
            font-weight: 700;
        }

        .num-hero {
            font-size: clamp(2.25rem, 9vw, 3.25rem);
            font-weight: 800;
            color: var(--gold);
            letter-spacing: .14em;
        }

        .num-lg {
            font-size: clamp(1.375rem, 4vw, 1.75rem);
        }

        .num-md {
            font-size: 1.0625rem;
        }

        .num-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .625rem;
            min-width: 4.25rem;
            border-radius: var(--r-sm);
            background: var(--surface-2);
            border: 1px solid var(--line);
            font-size: .9375rem;
        }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .75rem 1.125rem;
            min-height: 44px;
            border: 1px solid transparent;
            border-radius: var(--r-sm);
            font: inherit;
            font-size: .9375rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s, opacity .15s;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--ink-on-gold);
        }

        .btn-primary:hover {
            background: #fcd34d;
        }

        .btn-ghost {
            background: transparent;
            border-color: var(--line-strong);
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .btn-block {
            width: 100%;
        }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--gold);
            font-weight: 600;
            font-size: .875rem;
            text-decoration: none;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        /* ---------- Chips (date pickers / year filters) ---------- */
        .chip-row {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            scrollbar-width: none;
            padding-bottom: .125rem;
        }

        .chip-row::-webkit-scrollbar {
            display: none;
        }

        .chip-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .chip {
            flex: none;
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: .375rem .875rem;
            border-radius: 100px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            color: var(--text-muted);
            font-size: .8125rem;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, color .15s, border-color .15s;
        }

        .chip:hover {
            color: var(--text);
            border-color: var(--line-strong);
        }

        .chip.is-active {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--ink-on-gold);
            font-weight: 700;
        }

        /* ---------- Badges ---------- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .3rem .625rem;
            border-radius: 100px;
            border: 1px solid var(--line);
            background: var(--surface-2);
            color: var(--text-muted);
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-gold {
            background: var(--gold-soft);
            border-color: var(--gold-line);
            color: var(--gold);
        }

        .badge-green {
            background: var(--green-soft);
            border-color: var(--green-line);
            color: var(--green);
        }

        .badge-blue {
            background: var(--blue-soft);
            border-color: var(--blue-line);
            color: var(--blue);
        }

        /* ---------- Stats ---------- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: .75rem;
        }

        .stat {
            padding: 1rem;
            border-radius: var(--r-md);
            background: var(--surface);
            border: 1px solid var(--line);
            text-align: center;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .stat-label {
            margin-top: .25rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-dim);
        }

        /* ---------- Empty state ---------- */
        .empty {
            padding: clamp(2.5rem, 8vw, 4rem) 1.5rem;
            text-align: center;
            color: var(--text-dim);
        }

        .empty svg {
            margin: 0 auto .75rem;
            color: var(--text-dim);
            opacity: .7;
        }

        .empty-title {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 1rem;
        }

        /* ---------- Footer ---------- */
        .site-footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--line);
            background: var(--bg-elevated);
            padding: 1.75rem 0;
            margin-top: auto;
        }

        .footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            font-size: .8125rem;
            color: var(--text-dim);
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--gold);
        }

        .footer-source a {
            color: var(--gold);
        }

        /* Any wide block scrolls inside itself instead of the page. */
        .scroll-x {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <nav class="nav">
        <div class="shell nav-inner">
            <a href="{{ route('public.lottery-check') }}" class="brand">
                <span class="brand-mark"><x-icon name="crown" :size="19" /></span>
                <span>
                    <span class="brand-name">Kan San</span>
                    <span class="brand-sub">Thai Lottery</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="{{ route('public.lottery-check') }}" class="nav-link"
                    @if (request()->routeIs('public.lottery-check')) aria-current="page" @endif>
                    <x-icon name="scan-line" :size="16" />
                    <span>Check</span>
                </a>
                <a href="{{ route('public.lottery-history') }}" class="nav-link"
                    @if (request()->routeIs('public.lottery-history', 'public.lottery-result')) aria-current="page" @endif>
                    <x-icon name="history" :size="16" />
                    <span>History</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="shell">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell footer-inner">
            <p class="footer-source">
                Results source:
                <a href="https://xn--t3cmiit.com" target="_blank" rel="noopener noreferrer">ผลหวย.com</a>
                · Government Lottery Office
            </p>
            <div class="footer-links">
                <a href="{{ route('about-us') }}">About</a>
                <a href="{{ route('privacy-policy') }}">Privacy</a>
                <a href="{{ route('terms-conditions') }}">Terms</a>
            </div>
            <p>&copy; {{ date('Y') }} Kan San</p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
