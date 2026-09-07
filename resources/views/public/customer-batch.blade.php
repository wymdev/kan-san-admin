@extends('public.lottery.layout')

@section('title', 'Your Tickets')
@section('description', 'Your lottery tickets and their results.')

@push('styles')
    <style>
        .batch-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: clamp(1.25rem, 4vw, 1.75rem) var(--pad);
            border-radius: var(--r-lg);
            background: linear-gradient(120deg, var(--gold-soft), rgba(96, 165, 250, .06));
            border: 1px solid var(--gold-line);
            margin-bottom: 1.25rem;
        }

        .batch-head h1 {
            font-size: clamp(1.375rem, 4vw, 1.875rem);
            font-weight: 800;
        }

        .batch-head .who {
            margin-top: .25rem;
            color: var(--text-muted);
            font-size: .9375rem;
        }

        .win-banner {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem var(--pad);
            border-radius: var(--r-md);
            background: var(--green-soft);
            border: 1px solid var(--green-line);
            color: var(--green);
            font-weight: 600;
            margin-bottom: 1.25rem;
        }

        .group {
            margin-top: 2rem;
        }

        .group-head {
            display: flex;
            align-items: center;
            gap: .625rem;
            margin-bottom: .875rem;
        }

        .group-head h2 {
            font-size: 1.0625rem;
        }

        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(258px, 1fr));
            gap: .875rem;
        }

        .ticket {
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            overflow: hidden;
        }

        .ticket.is-win {
            border-color: var(--gold-line);
            background: linear-gradient(160deg, var(--gold-soft), var(--surface) 55%);
        }

        .ticket-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .ticket-mark {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: var(--r-sm);
            background: var(--surface-2);
            color: var(--text-muted);
            flex: none;
        }

        .ticket.is-win .ticket-mark {
            background: var(--gold);
            color: var(--ink-on-gold);
        }

        .ticket-mid {
            padding: .25rem 1rem 1rem;
            text-align: center;
        }

        .ticket-caption {
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--text-dim);
        }

        .ticket-number {
            font-size: clamp(1.875rem, 6vw, 2.375rem);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: .16em;
            margin-top: .25rem;
            color: var(--text);
        }

        .ticket.is-win .ticket-number {
            color: var(--gold);
        }

        .ticket-foot {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .625rem 1rem;
            border-top: 1px solid var(--line);
            background: rgba(0, 0, 0, .18);
            font-size: .75rem;
            color: var(--text-dim);
        }

        .ticket-foot span {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        /* Past (non-winning) tickets stay present but visually quiet. */
        .past-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: .625rem;
        }

        .past {
            padding: .75rem;
            border-radius: var(--r-md);
            background: var(--surface);
            border: 1px solid var(--line);
            text-align: center;
        }

        .past .n {
            font-size: 1.125rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            letter-spacing: .12em;
            color: var(--text-muted);
        }

        .past .d {
            margin-top: .125rem;
            font-size: .6875rem;
            color: var(--text-dim);
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
        $total = $stats['total'] ?? $transactions->count();
    @endphp

    <div class="batch-head">
        <div>
            <span class="eyebrow" style="margin-bottom:.25rem"><x-icon name="ticket" :size="13" /> Your tickets</span>
            <h1>{{ $customerName ?: 'Your lottery tickets' }}</h1>
            <p class="who">{{ $total }} {{ \Illuminate\Support\Str::plural('ticket', $total) }} in this batch</p>
        </div>
        @if ($batchNumber && $batchNumber !== 'N/A')
            <span class="badge badge-gold"><x-icon name="layers" :size="13" /> Batch #{{ $batchNumber }}</span>
        @endif
    </div>

    @if (($stats['won'] ?? 0) > 0)
        <p class="win-banner">
            <x-icon name="party-popper" :size="20" />
            Congratulations — {{ $stats['won'] }} of your {{ $total }}
            {{ \Illuminate\Support\Str::plural('ticket', $total) }}
            {{ $stats['won'] === 1 ? 'is a winner' : 'are winners' }}.
        </p>
    @endif

    <div class="stat-grid">
        <div class="stat">
            <div class="stat-value">{{ $total }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat" style="border-color:var(--gold-line);background:var(--gold-soft)">
            <div class="stat-value" style="color:var(--gold)">{{ $stats['won'] ?? 0 }}</div>
            <div class="stat-label">Won</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color:var(--blue)">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color:var(--text-dim)">{{ $stats['not_won'] ?? 0 }}</div>
            <div class="stat-label">Not won</div>
        </div>
    </div>

    {{-- ---------- Winners ---------- --}}
    @if ($winners->isNotEmpty())
        <section class="group">
            <div class="group-head">
                <h2>Winning tickets</h2>
                <span class="badge badge-gold">{{ $winners->count() }}</span>
            </div>
            <div class="ticket-grid">
                @foreach ($winners as $transaction)
                    <article class="ticket is-win">
                        <div class="ticket-top">
                            <span class="ticket-mark"><x-icon name="trophy" :size="17" /></span>
                            <span class="badge badge-gold">{{ $transaction->prize_won ?: 'Winner' }}</span>
                        </div>
                        <div class="ticket-mid">
                            <span class="ticket-caption">Ticket number</span>
                            <div class="ticket-number">{{ $transaction->secondaryTicket?->ticket_number ?? '——————' }}</div>
                        </div>
                        <div class="ticket-foot">
                            <span><x-icon name="layers" :size="12" />
                                Batch #{{ $transaction->secondaryTicket?->batch_number ?? '—' }}</span>
                            <span><x-icon name="calendar" :size="12" />
                                {{ $transaction->drawResult?->date_en ?? 'Confirmed' }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------- Pending ---------- --}}
    @if ($pending->isNotEmpty())
        <section class="group">
            <div class="group-head">
                <h2>Awaiting the draw</h2>
                <span class="badge badge-blue">{{ $pending->count() }}</span>
            </div>
            <div class="ticket-grid">
                @foreach ($pending as $transaction)
                    <article class="ticket">
                        <div class="ticket-top">
                            <span class="ticket-mark"><x-icon name="clock" :size="17" /></span>
                            <span class="badge badge-blue">Pending</span>
                        </div>
                        <div class="ticket-mid">
                            <span class="ticket-caption">Ticket number</span>
                            <div class="ticket-number">{{ $transaction->secondaryTicket?->ticket_number ?? '——————' }}</div>
                        </div>
                        <div class="ticket-foot">
                            <span><x-icon name="calendar" :size="12" />
                                @if ($transaction->secondaryTicket?->withdraw_date)
                                    Draw {{ $transaction->secondaryTicket->withdraw_date->format('d M Y') }}
                                @else
                                    Draw date to be announced
                                @endif
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------- Not won ---------- --}}
    @if ($notWon->isNotEmpty())
        <section class="group">
            <div class="group-head">
                <h2 style="color:var(--text-muted)">Not this time</h2>
                <span class="badge">{{ $notWon->count() }}</span>
            </div>
            <div class="past-grid">
                @foreach ($notWon as $transaction)
                    <div class="past">
                        <div class="n">{{ $transaction->secondaryTicket?->ticket_number ?? '——————' }}</div>
                        <div class="d">
                            {{ $transaction->secondaryTicket?->withdraw_date?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($total === 0)
        <div class="card" style="margin-top:1.25rem">
            <div class="empty">
                <x-icon name="inbox" :size="40" />
                <p class="empty-title">No tickets in this batch</p>
            </div>
        </div>
    @endif

    <p style="margin-top:2rem">
        <a href="{{ route('public.lottery-check') }}" class="btn btn-ghost">
            <x-icon name="scan-line" :size="16" /> Check other numbers
        </a>
    </p>
@endsection

@push('scripts')
    @if (($stats['won'] ?? 0) > 0)
        <div id="confetti" aria-hidden="true"></div>
        <script>
            (function () {
                // One short burst, skipped entirely when the visitor prefers reduced motion.
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
