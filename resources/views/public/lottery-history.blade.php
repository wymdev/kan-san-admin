@extends('public.lottery.layout')

@section('title', 'Past Results')
@section('description', 'Browse past Thai Government Lottery draw results by year.')

@push('styles')
    <style>
        .year-bar {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .year-bar .label {
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-dim);
        }

        .draw-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(268px, 1fr));
            gap: 1rem;
        }

        .draw-card {
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s, transform .15s;
        }

        .draw-card:hover {
            border-color: var(--gold-line);
            transform: translateY(-2px);
        }

        .draw-card-head {
            padding: .875rem 1.125rem;
            border-bottom: 1px solid var(--line);
            background: var(--bg-elevated);
        }

        .draw-card-head .en {
            font-weight: 700;
            font-size: .9375rem;
        }

        .draw-card-head .th {
            font-size: .75rem;
            color: var(--text-dim);
            margin-top: .125rem;
        }

        .draw-card-body {
            padding: 1.125rem;
            flex: 1;
        }

        .draw-first {
            text-align: center;
            padding: .875rem;
            border-radius: var(--r-md);
            background: var(--gold-soft);
            border: 1px solid var(--gold-line);
        }

        .draw-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .625rem 0;
            font-size: .8125rem;
        }

        .draw-line+.draw-line {
            border-top: 1px solid var(--line);
        }

        .draw-line .k {
            color: var(--text-dim);
        }

        .draw-card-foot {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            padding: .75rem;
            border-top: 1px solid var(--line);
            background: var(--bg-elevated);
            color: var(--gold);
            font-size: .8125rem;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <span class="eyebrow"><x-icon name="history" :size="13" /> Archive</span>
        <h1 class="page-title">Past draw results</h1>
        <p class="page-sub">Every published Thai Government Lottery draw, grouped by year.</p>
    </div>

    @if ($years->isNotEmpty())
        <div class="year-bar">
            <span class="label">Year</span>
            <div class="chip-wrap">
                @foreach ($years as $year)
                    <a href="{{ route('public.lottery-history', ['year' => $year]) }}"
                        class="chip {{ (int) $selectedYear === (int) $year ? 'is-active' : '' }}">
                        {{ $year + 543 }} <span style="opacity:.6;margin-left:.35rem">/ {{ $year }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($results->isNotEmpty())
        <div class="draw-grid">
            @foreach ($results as $result)
                @php
                    $prizes = $result->normalized_prizes ?? [];
                    $first = $prizes['first_prize'] ?? ($prizes['prizeFirst'] ?? ($prizes['prize_1'] ?? null));
                    $first = is_string($first) ? [$first] : (array) $first;

                    $running = collect($result->running_numbers ?? []);
                    $grab = function (string $needle) use ($running) {
                        $hit = $running->first(fn($i) => isset($i['id']) && stripos($i['id'], $needle) !== false);
                        $nums = $hit['number'] ?? [];
                        return is_string($nums) ? [$nums] : (array) $nums;
                    };
                    $back2 = $grab('BackTwo');
                    $back3 = $grab('BackThree');
                @endphp

                <a href="{{ route('public.lottery-result', $result->draw_date->format('Y-m-d')) }}" class="draw-card">
                    <div class="draw-card-head">
                        <div class="en">{{ $result->date_en ?? $result->draw_date->format('d M Y') }}</div>
                        @if ($result->date_th)
                            <div class="th">{{ $result->date_th }}</div>
                        @endif
                    </div>

                    <div class="draw-card-body">
                        <div class="draw-first">
                            <span class="prize-caption"
                                style="font-size:.625rem;letter-spacing:.14em;text-transform:uppercase;color:var(--text-dim);font-weight:700">
                                First prize
                            </span>
                            <div class="num num-lg" style="color:var(--gold);margin-top:.25rem">{{ $first[0] ?? '—' }}</div>
                        </div>

                        <div style="margin-top:.5rem">
                            <div class="draw-line">
                                <span class="k">Back 3 digits</span>
                                <span class="num num-md">{{ $back3 ? implode('  ', $back3) : '—' }}</span>
                            </div>
                            <div class="draw-line">
                                <span class="k">Last 2 digits</span>
                                <span class="num num-md">{{ $back2 ? implode('  ', $back2) : '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="draw-card-foot">
                        Full breakdown <x-icon name="arrow-right" :size="14" />
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="empty">
                <x-icon name="inbox" :size="40" />
                <p class="empty-title">No results for {{ $selectedYear + 543 }}</p>
                <p>Pick another year, or check back after the next draw.</p>
            </div>
        </div>
    @endif
@endsection
