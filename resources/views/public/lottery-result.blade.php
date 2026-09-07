@extends('public.lottery.layout')

@section('title', 'Ticket Result')
@section('description', 'Result for your lottery ticket.')

@push('styles')
    <style>
        .ticket-stage {
            max-width: 560px;
            margin: 0 auto;
        }

        /* The ticket keeps its physical metaphor: two panes split by a
           perforation, with a status stamp across the face. */
        .stub {
            position: relative;
            display: flex;
            border-radius: var(--r-lg);
            overflow: hidden;
            background: #f8fafc;
            color: #0f172a;
            box-shadow: var(--shadow);
        }

        .stub::before,
        .stub::after {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--bg);
            z-index: 2;
        }

        .stub-side {
            flex: none;
            width: 116px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: 1.25rem .75rem;
            background: #eef2f7;
            border-right: 2px dashed #cbd5e1;
        }

        .stub::before {
            top: -11px;
            left: 105px;
        }

        .stub::after {
            bottom: -11px;
            left: 105px;
        }

        .stub-logo {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(150deg, var(--gold), #f59e0b);
            color: var(--ink-on-gold);
        }

        .stub-price {
            text-align: center;
        }

        .stub-price .v {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .stub-price .u {
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .14em;
            color: #64748b;
        }

        .stub-main {
            flex: 1;
            min-width: 0;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .875rem;
        }

        .stub-meta {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .stub-owner {
            text-align: center;
            font-size: .8125rem;
            color: #64748b;
        }

        .stub-owner b {
            color: #1e293b;
        }

        .stub-number {
            text-align: center;
            font-size: clamp(2.25rem, 11vw, 3.25rem);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: .1em;
            color: #0f172a;
            line-height: 1.05;
        }

        .stub-date {
            text-align: center;
            font-size: .8125rem;
            color: #64748b;
        }

        .stub-foot {
            margin-top: auto;
            padding-top: .75rem;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
        }

        .stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 3;
            transform: translate(-50%, -50%) rotate(-14deg);
            padding: .375rem 1.25rem;
            border: 4px solid currentColor;
            border-radius: 8px;
            font-size: clamp(1.5rem, 6vw, 2.125rem);
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .82;
            pointer-events: none;
        }

        .stamp.won {
            color: #15803d;
        }

        .stamp.lost {
            color: #b91c1c;
        }

        .stamp.pending {
            color: #b45309;
        }

        .verdict {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-top: 1.25rem;
            padding: 1rem var(--pad);
            border-radius: var(--r-md);
            border: 1px solid var(--line);
            background: var(--surface);
        }

        .verdict.is-win {
            border-color: var(--gold-line);
            background: var(--gold-soft);
        }

        .verdict .t {
            font-weight: 700;
        }

        .verdict .s {
            font-size: .8125rem;
            color: var(--text-muted);
        }

        @media (max-width: 560px) {
            .stub {
                flex-direction: column;
            }

            .stub-side {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                border-right: 0;
                border-bottom: 2px dashed #cbd5e1;
            }

            .stub::before {
                top: auto;
                bottom: auto;
                left: -11px;
                top: 96px;
            }

            .stub::after {
                bottom: auto;
                left: auto;
                right: -11px;
                top: 96px;
            }
        }

        #confetti {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 60;
            overflow: hidden;
        }

        .confetti-bit {
            position: absolute;
            top: -5vh;
            width: 9px;
            height: 9px;
            border-radius: 2px;
            animation: fall linear forwards;
        }

        @keyframes fall {
            to {
                transform: translateY(105vh) rotate(720deg);
                opacity: 0;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $status = $transaction->status;
        $stamp = ['won' => 'Winner', 'not_won' => 'No win', 'pending' => 'Pending'][$status] ?? 'Pending';
        $stampClass = ['won' => 'won', 'not_won' => 'lost'][$status] ?? 'pending';
    @endphp

    <div class="page-head" style="text-align:center">
        <span class="eyebrow" style="justify-content:center"><x-icon name="shield-check" :size="13" /> Verified result</span>
        <h1 class="page-title">Your ticket</h1>
        <p class="page-sub">Checked against the official Government Lottery Office draw.</p>
    </div>

    <div class="ticket-stage">
        <div class="stub">
            <div class="stamp {{ $stampClass }}">{{ $stamp }}</div>

            <div class="stub-side">
                <span class="stub-logo"><x-icon name="crown" :size="24" /></span>
                <div class="stub-price">
                    <div class="v">100</div>
                    <div class="u">Baht</div>
                </div>
            </div>

            <div class="stub-main">
                <div class="stub-meta">
                    <span>Thai Gov Lottery</span>
                    <span>#{{ substr($transaction->public_token, 0, 8) }}</span>
                </div>

                @if ($transaction->customer)
                    <p class="stub-owner">Owner <b>{{ $transaction->customer->customer_name }}</b></p>
                @endif

                <div>
                    <div class="stub-number">{{ $ticket?->ticket_number ?? '——————' }}</div>
                    <p class="stub-date">
                        {{ $ticket?->withdraw_date?->format('d F Y') ?? 'Draw date to be announced' }}
                    </p>
                </div>

                <p class="stub-foot">Period {{ $ticket?->batch_number ?? '—' }}</p>
            </div>
        </div>

        <div class="verdict {{ $status === 'won' ? 'is-win' : '' }}">
            <x-icon name="{{ $status === 'won' ? 'party-popper' : ($status === 'not_won' ? 'circle-slash' : 'clock') }}"
                :size="22" style="color:{{ $status === 'won' ? 'var(--gold)' : 'var(--text-dim)' }};flex:none" />
            <div>
                @if ($status === 'won')
                    <div class="t" style="color:var(--gold)">{{ $transaction->prize_won ?: 'This ticket won a prize' }}</div>
                    <div class="s">Contact us to arrange your payout.</div>
                @elseif ($status === 'not_won')
                    <div class="t">No prize this draw</div>
                    <div class="s">
                        {{ $drawResult?->date_en ? 'Checked against the ' . $drawResult->date_en . ' draw.' : 'Better luck next time.' }}
                    </div>
                @else
                    <div class="t">Awaiting the draw</div>
                    <div class="s">We'll check this ticket as soon as results are published.</div>
                @endif
            </div>
        </div>

        <p style="margin-top:1.25rem;text-align:center">
            <a href="{{ route('public.lottery-check') }}" class="btn btn-ghost">
                <x-icon name="scan-line" :size="16" /> Check another ticket
            </a>
        </p>
    </div>
@endsection

@push('scripts')
    @if ($transaction->status === 'won')
        <div id="confetti" aria-hidden="true"></div>
        <script>
            (function () {
                // One short burst rather than the previous endless canvas loop.
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const host = document.getElementById('confetti');
                const colors = ['#fbbf24', '#fcd34d', '#34d399', '#60a5fa', '#f8fafc'];

                for (let i = 0; i < 40; i++) {
                    const bit = document.createElement('div');
                    bit.className = 'confetti-bit';
                    bit.style.left = Math.random() * 100 + '%';
                    bit.style.background = colors[i % colors.length];
                    bit.style.animationDuration = (2.4 + Math.random() * 1.6) + 's';
                    bit.style.animationDelay = (Math.random() * 0.8) + 's';
                    host.appendChild(bit);
                }

                setTimeout(() => host.remove(), 5500);
            })();
        </script>
    @endif
@endpush
