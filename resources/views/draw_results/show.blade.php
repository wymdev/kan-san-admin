@extends('layouts.vertical', ['title' => 'Lottery Draw Details'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Draw Result Details'])

    @php
        $drawDate = \Carbon\Carbon::parse($result->draw_date);
        $totalNumbers =
            \App\Support\PrizeLabels::countNumbers($prizes) + \App\Support\PrizeLabels::countNumbers($running_numbers);
    @endphp

    {{-- ---------- Header ---------- --}}
    <div class="card mb-5">
        <div class="card-body">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-default-500">
                        <i class="size-4" data-lucide="calendar-check"></i>
                        Draw date
                    </div>
                    <h2 class="mt-1 text-3xl font-bold text-default-900">
                        {{ $result->date_en ?? $drawDate->format('d F Y') }}
                    </h2>
                    <p class="mt-1 text-sm text-default-500">
                        {{ $result->date_th }} · {{ $drawDate->format('Y-m-d') }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if ($drawDate->isToday())
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-green-500/25 bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400">
                                <i class="size-3.5" data-lucide="zap"></i> Latest draw
                            </span>
                        @elseif ($drawDate->isCurrentMonth())
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-primary/25 bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                                <i class="size-3.5" data-lucide="clock"></i> Recent draw
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-default-200 bg-default-100 px-2.5 py-1 text-xs font-medium text-default-500">
                                <i class="size-3.5" data-lucide="archive"></i> Archived
                            </span>
                        @endif

                        <span
                            class="inline-flex items-center gap-1.5 rounded-md border border-default-200 bg-default-100 px-2.5 py-1 text-xs font-medium text-default-600">
                            {{ count($prizes) }} prize tiers · {{ number_format($totalNumbers) }} numbers
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    @if ($result->endpoint)
                        <a href="{{ $result->endpoint }}" target="_blank" rel="noopener noreferrer"
                            class="btn btn-sm bg-default-100 text-default-700 hover:bg-default-200">
                            <i class="size-4 me-1.5" data-lucide="external-link"></i>API source
                        </a>
                    @endif
                    <a href="{{ route('draw_results.index') }}"
                        class="btn btn-sm bg-primary text-white hover:bg-primary/90">
                        <i class="size-4 me-1.5" data-lucide="arrow-left"></i>Back to list
                    </a>
                </div>
            </div>

            <p class="mt-4 border-t border-default-200 pt-3 text-xs text-default-500">
                Results source:
                <a href="https://xn--t3cmiit.com" target="_blank" rel="noopener noreferrer"
                    class="underline hover:text-default-700">ผลหวย.com</a>
                · Government Lottery Office
            </p>
        </div>
    </div>

    {{-- ---------- Prize categories ---------- --}}
    @if (count($prizes))
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="flex items-center gap-2 text-base font-semibold text-default-900">
                    <i class="size-5 text-default-500" data-lucide="trophy"></i>
                    Prize categories
                </h3>
            </div>
            <div class="card-body space-y-3">
                @foreach ($prizes as $prize)
                    @php
                        $numbers = (array) ($prize['number'] ?? []);
                        $headline = ($prize['name'] ?? '') === 'First Prize';
                    @endphp
                    <div
                        class="rounded-xl border p-5 {{ $headline ? 'border-amber-500/30 bg-amber-500/5' : 'border-default-200' }}">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <h4
                                class="flex items-center gap-2 text-base font-semibold {{ $headline ? 'text-amber-700 dark:text-amber-400' : 'text-default-900' }}">
                                <i class="size-4" data-lucide="{{ $headline ? 'trophy' : 'hash' }}"></i>
                                {{ $prize['name'] }}
                            </h4>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-green-500/25 bg-green-500/10 px-2.5 py-1 text-xs font-semibold text-green-600 dark:text-green-400">
                                <i class="size-3.5" data-lucide="banknote"></i>
                                ฿{{ number_format((int) ($prize['reward'] ?? 0)) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($numbers as $number)
                                <span
                                    class="rounded-lg border px-3 py-1.5 font-mono font-bold tabular-nums tracking-widest {{ $headline ? 'border-amber-500/30 bg-amber-500/10 text-lg text-amber-700 dark:text-amber-400' : 'border-default-200 bg-default-100 text-default-800' }}">
                                    {{ $number }}
                                </span>
                            @endforeach
                        </div>

                        @if (count($numbers) > 5)
                            <p class="mt-4 border-t border-default-200 pt-3 text-xs text-default-500">
                                {{ count($numbers) }} winning numbers in this tier
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ---------- Running numbers ---------- --}}
    @if (count($running_numbers))
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="flex items-center gap-2 text-base font-semibold text-default-900">
                    <i class="size-5 text-default-500" data-lucide="list-ordered"></i>
                    Running numbers
                </h3>
            </div>
            <div class="card-body space-y-3">
                @foreach ($running_numbers as $running)
                    @php $numbers = (array) ($running['number'] ?? []); @endphp
                    <div class="rounded-xl border border-default-200 p-5">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <h4 class="flex items-center gap-2 text-base font-semibold text-default-900">
                                <i class="size-4" data-lucide="hash"></i>
                                {{ $running['name'] }}
                            </h4>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-green-500/25 bg-green-500/10 px-2.5 py-1 text-xs font-semibold text-green-600 dark:text-green-400">
                                <i class="size-3.5" data-lucide="banknote"></i>
                                ฿{{ number_format((int) ($running['reward'] ?? 0)) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($numbers as $number)
                                <span
                                    class="rounded-lg border border-default-200 bg-default-100 px-3 py-1.5 font-mono font-bold tabular-nums tracking-widest text-default-800">
                                    {{ $number }}
                                </span>
                            @endforeach
                        </div>

                        @if (count($numbers) > 10)
                            <p class="mt-4 border-t border-default-200 pt-3 text-xs text-default-500">
                                {{ count($numbers) }} numbers
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (!count($prizes) && !count($running_numbers))
        <div class="card">
            <div class="card-body py-12">
                <x-ui.empty-state title="No prize data for this draw"
                    description="This record has no prize categories stored. Try syncing results again." />
            </div>
        </div>
    @endif
@endsection
