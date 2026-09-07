@extends('public.lottery.layout')

@section('title', 'Results · ' . ($drawResult->date_en ?? $drawResult->draw_date->format('d M Y')))
@section('description', 'Full Thai Government Lottery prize breakdown for ' . ($drawResult->date_en ?? $drawResult->draw_date->format('d M Y')) . '.')

@push('styles')
    <style>
        .draw-banner {
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

        .draw-banner .date-en {
            font-size: clamp(1.375rem, 4vw, 1.875rem);
            font-weight: 800;
        }

        .draw-banner .date-th {
            color: var(--text-muted);
            margin-top: .125rem;
            font-size: .9375rem;
        }

        .tier-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            padding: 1.125rem var(--pad);
        }

        .tier-card+.tier-card {
            margin-top: .75rem;
        }

        .tier-card.is-headline {
            background: linear-gradient(120deg, var(--gold-soft), var(--surface) 65%);
            border-color: var(--gold-line);
        }

        .tier-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .625rem;
            margin-bottom: .875rem;
        }

        .tier-card-head h3 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1rem;
        }

        .tier-card.is-headline h3 {
            color: var(--gold);
        }

        .tier-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .tier-card.is-headline .tier-numbers {
            justify-content: center;
        }

        .tier-card.is-headline .num-chip {
            background: rgba(251, 191, 36, .16);
            border-color: var(--gold-line);
            color: var(--gold);
            font-size: clamp(1.75rem, 6vw, 2.5rem);
            padding: .625rem 1.25rem;
            min-width: 0;
        }

        .tier-foot {
            margin-top: .875rem;
            padding-top: .75rem;
            border-top: 1px solid var(--line);
            font-size: .75rem;
            color: var(--text-dim);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--text-dim);
            margin: 1.75rem 0 .75rem;
        }
    </style>
@endpush

@section('content')
    <p style="margin-bottom:1rem">
        <a href="{{ route('public.lottery-history') }}" class="btn-link">
            <x-icon name="arrow-left" :size="14" /> All past results
        </a>
    </p>

    <div class="draw-banner">
        <div>
            <span class="eyebrow" style="margin-bottom:.25rem"><x-icon name="trophy" :size="13" /> Official draw</span>
            <div class="date-en">{{ $drawResult->date_en ?? $drawResult->draw_date->format('d F Y') }}</div>
            @if ($drawResult->date_th)
                <div class="date-th">{{ $drawResult->date_th }}</div>
            @endif
        </div>
        <div class="chip-wrap">
            <span class="badge badge-gold">{{ count($prizes) }} prize tiers</span>
            <span class="badge">{{ number_format(\App\Support\PrizeLabels::countNumbers($prizes) + \App\Support\PrizeLabels::countNumbers($runningNumbers)) }} winning numbers</span>
        </div>
    </div>

    @if ($prizes)
        <h2 class="section-title"><x-icon name="trophy" :size="13" /> Prize categories</h2>

        @foreach ($prizes as $prize)
            @php
                $numbers = (array) ($prize['number'] ?? []);
                $headline = ($prize['name'] ?? '') === 'First Prize';
                $reward = is_numeric($prize['reward'] ?? null) ? number_format((float) $prize['reward']) : ($prize['reward'] ?? null);
            @endphp
            <article class="tier-card {{ $headline ? 'is-headline' : '' }}">
                <div class="tier-card-head">
                    <h3>
                        <x-icon name="{{ $headline ? 'trophy' : 'hash' }}" :size="17" />
                        {{ $prize['name'] ?? 'Prize' }}
                    </h3>
                    @if ($reward)
                        <span class="badge badge-green"><x-icon name="banknote" :size="13" /> ฿{{ $reward }}</span>
                    @endif
                </div>

                <div class="tier-numbers">
                    @foreach ($numbers as $number)
                        <span class="num num-chip">{{ $number }}</span>
                    @endforeach
                </div>

                @if (count($numbers) > 5)
                    <p class="tier-foot">{{ count($numbers) }} winning numbers in this tier</p>
                @endif
            </article>
        @endforeach
    @endif

    @if ($runningNumbers)
        <h2 class="section-title"><x-icon name="hash" :size="13" /> Running numbers</h2>

        @foreach ($runningNumbers as $running)
            @php
                $numbers = (array) ($running['number'] ?? []);
                $reward = is_numeric($running['reward'] ?? null) ? number_format((float) $running['reward']) : ($running['reward'] ?? null);
            @endphp
            <article class="tier-card">
                <div class="tier-card-head">
                    <h3><x-icon name="hash" :size="17" /> {{ $running['name'] ?? 'Running numbers' }}</h3>
                    @if ($reward)
                        <span class="badge badge-green"><x-icon name="banknote" :size="13" /> ฿{{ $reward }}</span>
                    @endif
                </div>
                <div class="tier-numbers">
                    @foreach ($numbers as $number)
                        <span class="num num-chip">{{ $number }}</span>
                    @endforeach
                </div>
            </article>
        @endforeach
    @endif

    @if (! $prizes && ! $runningNumbers)
        <div class="card">
            <div class="empty">
                <x-icon name="inbox" :size="40" />
                <p class="empty-title">No prize data for this draw</p>
            </div>
        </div>
    @endif

    <p style="margin-top:1.75rem">
        <a href="{{ route('public.lottery-check', ['date' => $drawResult->draw_date->format('Y-m-d')]) }}"
            class="btn btn-primary">
            <x-icon name="scan-line" :size="17" /> Check your numbers for this draw
        </a>
    </p>
@endsection
