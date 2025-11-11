@extends('layouts.vertical', ['title' => 'Lottery Draw Details'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Draw Result Details'])

    <!-- Header Section -->
    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="size-8 text-primary" data-lucide="calendar-check"></i>
                            <div>
                                <h2 class="text-3xl font-bold text-default-900">{{ $result->date_en }}</h2>
                                <p class="text-default-500 text-sm mt-1">
                                    <i class="size-3.5 inline" data-lucide="calendar"></i>
                                    {{ $result->date_th }} • Draw Date: {{ \Carbon\Carbon::parse($result->draw_date)->format('F d, Y') }}
                                </p>
                            </div>
                        </div>
                        
                        @if(\Carbon\Carbon::parse($result->draw_date)->isToday())
                            <span class="inline-flex items-center gap-1 py-1 px-3 rounded-full text-xs font-semibold bg-green-100 border border-green-200 text-green-700">
                                <i class="size-3.5" data-lucide="zap"></i> Latest Draw
                            </span>
                        @elseif(\Carbon\Carbon::parse($result->draw_date)->isCurrentMonth())
                            <span class="inline-flex items-center gap-1 py-1 px-3 rounded-full text-xs font-semibold bg-purple-100 border border-purple-200 text-purple-700">
                                <i class="size-3.5" data-lucide="clock"></i> Recent Draw
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 py-1 px-3 rounded-full text-xs font-semibold bg-default-100 border border-default-200 text-default-600">
                                <i class="size-3.5" data-lucide="archive"></i> Archived
                            </span>
                        @endif
                    </div>

                    <div class="flex gap-2 flex-shrink-0">
                        @if($result->endpoint)
                            <a href="{{ $result->endpoint }}" target="_blank" class="btn btn-sm bg-default-100 text-default-700 hover:bg-default-200">
                                <i class="size-4 me-1.5" data-lucide="external-link"></i>
                                View API Source
                            </a>
                        @endif
                        <a href="{{ route('draw_results.index') }}" class="btn btn-sm bg-primary text-white hover:bg-primary-600">
                            <i class="size-4 me-1.5" data-lucide="arrow-left"></i>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Prizes Section -->
    @if(count($prizes) > 0)
        <div class="grid grid-cols-1 gap-5 mb-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-xl font-bold text-default-900 flex items-center gap-2">
                        <i class="size-6 text-yellow-600" data-lucide="trophy"></i>
                        Prize Categories
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @foreach($prizes as $prize)
                            @php
                                $numbers = is_array($prize['number']) ? $prize['number'] : [$prize['number']];
                                $reward = number_format(intval($prize['reward']));
                                
                                // Styling based on prize type
                                $cardClass = 'border-2 rounded-xl p-5 transition-all hover:shadow-lg';
                                $badgeClass = 'inline-flex items-center py-2 px-4 rounded-lg font-bold font-mono text-lg';
                                $iconHtml = '';
                                $headerClass = 'text-xl font-bold mb-3';
                                
                                if ($prize['name'] === 'First Prize') {
                                    $cardClass .= ' border-yellow-400 bg-gradient-to-br from-yellow-50 via-amber-50 to-yellow-100';
                                    $badgeClass .= ' bg-gradient-to-r from-yellow-300 to-amber-400 text-yellow-900 shadow-md border-2 border-yellow-500';
                                    $iconHtml = '<i class="size-6 text-yellow-600" data-lucide="trophy"></i>';
                                    $headerClass .= ' text-yellow-900';
                                } elseif ($prize['name'] === '1st Prize Neighbor') {
                                    $cardClass .= ' border-blue-300 bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100';
                                    $badgeClass .= ' bg-gradient-to-r from-blue-200 to-cyan-200 text-blue-900 border-2 border-blue-400';
                                    $iconHtml = '<i class="size-5 text-blue-600" data-lucide="star"></i>';
                                    $headerClass .= ' text-blue-900';
                                } elseif ($prize['name'] === 'Second Prize') {
                                    $cardClass .= ' border-gray-300 bg-gradient-to-br from-gray-50 via-slate-50 to-gray-100';
                                    $badgeClass .= ' bg-gradient-to-r from-gray-200 to-slate-200 text-gray-900 border-2 border-gray-400';
                                    $iconHtml = '<i class="size-5 text-gray-600" data-lucide="award"></i>';
                                    $headerClass .= ' text-gray-900';
                                } elseif ($prize['name'] === 'Third Prize') {
                                    $cardClass .= ' border-orange-300 bg-gradient-to-br from-orange-50 via-amber-50 to-orange-100';
                                    $badgeClass .= ' bg-gradient-to-r from-orange-200 to-amber-200 text-orange-900 border-2 border-orange-400';
                                    $iconHtml = '<i class="size-5 text-orange-600" data-lucide="medal"></i>';
                                    $headerClass .= ' text-orange-900';
                                } elseif (str_contains($prize['name'], 'Fourth')) {
                                    $cardClass .= ' border-green-300 bg-gradient-to-br from-green-50 to-emerald-100';
                                    $badgeClass .= ' bg-gradient-to-r from-green-200 to-emerald-200 text-green-900 border-2 border-green-400';
                                    $iconHtml = '<i class="size-5 text-green-600" data-lucide="gift"></i>';
                                    $headerClass .= ' text-green-900';
                                } elseif (str_contains($prize['name'], 'Fifth')) {
                                    $cardClass .= ' border-indigo-300 bg-gradient-to-br from-indigo-50 to-blue-100';
                                    $badgeClass .= ' bg-gradient-to-r from-indigo-200 to-blue-200 text-indigo-900 border-2 border-indigo-400';
                                    $iconHtml = '<i class="size-5 text-indigo-600" data-lucide="sparkles"></i>';
                                    $headerClass .= ' text-indigo-900';
                                } else {
                                    $cardClass .= ' border-purple-300 bg-gradient-to-br from-purple-50 via-pink-50 to-purple-100';
                                    $badgeClass .= ' bg-gradient-to-r from-purple-200 to-pink-200 text-purple-900 border-2 border-purple-400';
                                    $iconHtml = '<i class="size-5 text-purple-600" data-lucide="hash"></i>';
                                    $headerClass .= ' text-purple-900';
                                }
                            @endphp
                            
                            <div class="{{ $cardClass }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                                    <h4 class="{{ $headerClass }} flex items-center gap-2">
                                        {!! $iconHtml !!}
                                        {{ $prize['name'] }}
                                    </h4>
                                    <span class="inline-flex items-center gap-1.5 py-1.5 px-4 rounded-lg text-sm font-bold bg-green-100 border-2 border-green-300 text-green-700 shadow-sm">
                                        <i class="size-4" data-lucide="banknote"></i>
                                        ฿{{ $reward }}
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap gap-3">
                                    @foreach($numbers as $num)
                                        <span class="{{ $badgeClass }}">
                                            {{ $num }}
                                        </span>
                                    @endforeach
                                </div>
                                
                                @if(count($numbers) > 5)
                                    <div class="mt-3 pt-3 border-t border-default-200">
                                        <span class="text-sm text-default-600 flex items-center gap-1.5">
                                            <i class="size-4" data-lucide="list"></i>
                                            Total: <span class="font-semibold">{{ count($numbers) }}</span> winning numbers
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Running Numbers Section -->
    @if(count($running_numbers) > 0)
        <div class="grid grid-cols-1 gap-5 mb-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-xl font-bold text-default-900 flex items-center gap-2">
                        <i class="size-6 text-indigo-600" data-lucide="list-ordered"></i>
                        Running Numbers
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @foreach($running_numbers as $rn)
                            @php
                                $numbers = is_array($rn['number']) ? $rn['number'] : [$rn['number']];
                                $reward = number_format(intval($rn['reward']));
                            @endphp
                            
                            <div class="border-2 border-indigo-300 rounded-xl p-5 bg-gradient-to-br from-indigo-50 via-blue-50 to-indigo-100 hover:shadow-lg transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                                    <h5 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                                        <i class="size-5 text-indigo-600" data-lucide="hash"></i>
                                        {{ $rn['name'] }}
                                    </h5>
                                    <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-sm font-bold bg-green-100 border-2 border-green-300 text-green-700">
                                        <i class="size-3.5" data-lucide="banknote"></i>
                                        ฿{{ $reward }}
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    @foreach($numbers as $num)
                                        <span class="inline-flex items-center py-1.5 px-3 rounded-lg text-base font-mono font-bold bg-white border-2 border-indigo-300 text-indigo-900 shadow-sm">
                                            {{ $num }}
                                        </span>
                                    @endforeach
                                </div>
                                
                                @if(count($numbers) > 10)
                                    <div class="mt-3 pt-3 border-t border-indigo-200">
                                        <span class="text-sm text-indigo-700 flex items-center gap-1.5">
                                            <i class="size-4" data-lucide="list"></i>
                                            Total: <span class="font-semibold">{{ count($numbers) }}</span> numbers
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Statistics Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
        <div class="card hover:shadow-lg transition-shadow">
            <div class="card-body text-center">
                <div class="inline-flex items-center justify-center size-14 rounded-full bg-yellow-100 text-yellow-600 mb-3">
                    <i class="size-7" data-lucide="award"></i>
                </div>
                <h4 class="text-2xl font-bold text-default-900">{{ count($prizes) }}</h4>
                <p class="text-sm text-default-500 mt-1">Prize Categories</p>
            </div>
        </div>
        
        <div class="card hover:shadow-lg transition-shadow">
            <div class="card-body text-center">
                <div class="inline-flex items-center justify-center size-14 rounded-full bg-indigo-100 text-indigo-600 mb-3">
                    <i class="size-7" data-lucide="hash"></i>
                </div>
                <h4 class="text-2xl font-bold text-default-900">{{ count($running_numbers) }}</h4>
                <p class="text-sm text-default-500 mt-1">Running Number Types</p>
            </div>
        </div>
        
        <div class="card hover:shadow-lg transition-shadow">
            <div class="card-body text-center">
                <div class="inline-flex items-center justify-center size-14 rounded-full bg-green-100 text-green-600 mb-3">
                    <i class="size-7" data-lucide="trophy"></i>
                </div>
                @php
                    $totalWinners = collect($prizes)->sum(fn($p) => count(is_array($p['number']) ? $p['number'] : [$p['number']]));
                    $totalRunning = collect($running_numbers)->sum(fn($r) => count(is_array($r['number']) ? $r['number'] : [$r['number']]));
                @endphp
                <h4 class="text-2xl font-bold text-default-900">{{ number_format($totalWinners + $totalRunning) }}</h4>
                <p class="text-sm text-default-500 mt-1">Total Winning Numbers</p>
            </div>
        </div>
    </div>
@endsection
