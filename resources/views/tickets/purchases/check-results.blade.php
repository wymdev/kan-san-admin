@extends('layouts.vertical', ['title' => 'Check Lottery Results'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Admin', 'title' => 'Check Lottery Results'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    @if ($message = Session::get('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    @if ($message = Session::get('info'))
        <div class="bg-info/10 border border-info/20 text-info px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    {{-- Latest Draw Info --}}
    <div class="card mb-6 bg-gradient-to-r from-primary/5 to-primary/10 border-primary/20">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-default-600 mb-1">Latest Draw Results Available</p>
                    <h3 class="text-2xl font-bold text-primary">{{ $stats['latest_draw_date'] }}</h3>
                    <p class="text-sm text-default-500 mt-1">{{ $stats['latest_draw_date_th'] }}</p>
                </div>
                <div class="p-4 bg-primary/10 rounded-full">
                    <i class="text-primary size-10" data-lucide="calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Overview --}}
    <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-default-600 mb-1">Ready to Check</p>
                        <h3 class="text-3xl font-bold text-success">{{ $statusGroups['ready_to_check']->count() }}</h3>
                        <p class="text-xs text-default-500 mt-1">Can be checked now</p>
                    </div>
                    <div class="p-4 bg-success/10 rounded-full">
                        <i class="text-success size-8" data-lucide="check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-default-600 mb-1">Waiting for Draw</p>
                        <h3 class="text-3xl font-bold text-warning">{{ $statusGroups['waiting_for_draw']->count() }}</h3>
                        <p class="text-xs text-default-500 mt-1">Future draws</p>
                    </div>
                    <div class="p-4 bg-warning/10 rounded-full">
                        <i class="text-warning size-8" data-lucide="clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-default-600 mb-1">Winners Found</p>
                        <h3 class="text-3xl font-bold text-purple-600">{{ $stats['won'] }}</h3>
                        <p class="text-xs text-default-500 mt-1">All time</p>
                    </div>
                    <div class="p-4 bg-purple-100 rounded-full">
                        <i class="text-purple-600 size-8" data-lucide="trophy"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-default-600 mb-1">Total Checked</p>
                        <h3 class="text-3xl font-bold text-primary">{{ $stats['won'] + $stats['not_won'] }}</h3>
                        <p class="text-xs text-default-500 mt-1">All time</p>
                    </div>
                    <div class="p-4 bg-primary/10 rounded-full">
                        <i class="text-primary size-8" data-lucide="check-square"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('purchases.check-results') }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn bg-primary text-white btn-lg" 
                            onclick="return confirm('This will check all approved purchases against the latest draw results. Continue?')"
                            @if($statusGroups['ready_to_check']->isEmpty()) disabled @endif>
                        <i class="size-5" data-lucide="search"></i>
                        <span>Check All Results ({{ $statusGroups['ready_to_check']->count() }})</span>
                    </button>
                </form>

                <form action="{{ route('purchases.notify-results') }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn bg-success text-white btn-lg" 
                            onclick="return confirm('Send push notifications to customers with recently checked results?')">
                        <i class="size-5" data-lucide="send"></i>
                        <span>Notify All Customers</span>
                    </button>
                </form>

                <a href="{{ route('purchases.index') }}" class="btn bg-default-200 text-default-700">
                    <i class="size-5" data-lucide="arrow-left"></i>
                    <span>Back to Purchases</span>
                </a>
            </div>

            @if($statusGroups['ready_to_check']->isEmpty())
                <div class="mt-3 p-3 bg-info/10 border border-info/20 rounded">
                    <p class="text-sm text-info-700">
                        <i class="size-4 inline" data-lucide="info"></i>
                        <strong>No purchases ready to check.</strong> All purchases are either waiting for future draws or have already been checked.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabs for Different Status Groups --}}
    <div class="card mb-6">
        <div class="card-header border-b">
            <ul class="nav nav-tabs flex-wrap text-sm font-medium text-center text-default-600">
                <li class="me-2">
                    <a href="#ready" data-fc-target="#ready" data-fc-type="tab" 
                       class="inline-block px-4 py-2 rounded-t-lg hover:text-primary active">
                        ✅ Ready to Check ({{ $statusGroups['ready_to_check']->count() }})
                    </a>
                </li>
                <li class="me-2">
                    <a href="#waiting" data-fc-target="#waiting" data-fc-type="tab" 
                       class="inline-block px-4 py-2 rounded-t-lg hover:text-primary">
                        ⏰ Waiting ({{ $statusGroups['waiting_for_draw']->count() }})
                    </a>
                </li>
                <li class="me-2">
                    <a href="#outdated" data-fc-target="#outdated" data-fc-type="tab" 
                       class="inline-block px-4 py-2 rounded-t-lg hover:text-primary">
                        ⚠️ Outdated ({{ $statusGroups['outdated']->count() }})
                    </a>
                </li>
                <li class="me-2">
                    <a href="#winners" data-fc-target="#winners" data-fc-type="tab" 
                       class="inline-block px-4 py-2 rounded-t-lg hover:text-primary">
                        🏆 Recent Winners
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            {{-- Ready to Check Tab --}}
            <div id="ready" class="active">
                @if($readyToCheck->count() > 0)
                    <div class="space-y-3">
                        @foreach($readyToCheck as $purchase)
                            <div class="flex items-center justify-between p-4 bg-success/5 border border-success/20 rounded-lg hover:bg-success/10 transition">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-lg">{{ $purchase->order_number }}</span>
                                        <span class="inline-flex px-2 py-0.5 bg-success/20 text-success rounded text-xs font-medium">
                                            Ready
                                        </span>
                                    </div>
                                    <div class="text-sm text-default-600 mt-1">
                                        👤 {{ $purchase->customer->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-default-500 mt-1">
                                        🎫 {{ $purchase->lotteryTicket->ticket_name ?? '-' }}
                                    </div>
                                    <div class="flex items-center gap-4 mt-2">
                                        <div class="text-xs">
                                            <span class="text-default-500">Ticket Draw:</span>
                                            <span class="font-medium text-primary">
                                                {{ \Carbon\Carbon::parse($purchase->lotteryTicket->withdraw_date)->format('M d, Y') }}
                                            </span>
                                        </div>
                                        @if(isset($purchase->status_info['postponement']))
                                            <span class="inline-flex px-2 py-0.5 bg-info/10 text-info rounded text-xs">
                                                {{ $purchase->status_info['postponement'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" 
                                       class="btn btn-sm bg-primary/10 text-primary hover:bg-primary/20">
                                        <i class="size-4" data-lucide="eye"></i>
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $readyToCheck->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="size-16 mx-auto mb-4 text-default-300" data-lucide="check-circle"></i>
                        <p class="text-default-500">No purchases ready to check at this time.</p>
                    </div>
                @endif
            </div>

            {{-- Waiting for Draw Tab --}}
            <div id="waiting" class="hidden">
                @if($statusGroups['waiting_for_draw']->count() > 0)
                    <div class="space-y-3">
                        @foreach($statusGroups['waiting_for_draw'] as $purchase)
                            <div class="flex items-center justify-between p-4 bg-warning/5 border border-warning/20 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-lg">{{ $purchase->order_number }}</span>
                                        <span class="inline-flex px-2 py-0.5 bg-warning/20 text-warning rounded text-xs font-medium">
                                            Future Draw
                                        </span>
                                    </div>
                                    <div class="text-sm text-default-600 mt-1">
                                        👤 {{ $purchase->customer->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-default-500 mt-1">
                                        🎫 {{ $purchase->lotteryTicket->ticket_name ?? '-' }}
                                    </div>
                                    <div class="mt-2 p-2 bg-warning/10 rounded">
                                        <div class="text-xs">
                                            <div class="font-medium text-warning mb-1">⏰ Waiting for Draw Results</div>
                                            <div class="text-default-600">
                                                Expected Draw: <span class="font-medium">
                                                    {{ $purchase->status_info['expected_result_date'] ?? 'TBD' }}
                                                </span>
                                            </div>
                                            <div class="text-default-500 mt-1">
                                                ({{ $purchase->status_info['days_difference'] ?? 0 }} days from now)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="size-16 mx-auto mb-4 text-default-300" data-lucide="clock"></i>
                        <p class="text-default-500">No purchases waiting for future draws.</p>
                    </div>
                @endif
            </div>

            {{-- Outdated Tab --}}
            <div id="outdated" class="hidden">
                @if($statusGroups['outdated']->count() > 0)
                    <div class="mb-4 p-4 bg-danger/10 border border-danger/20 rounded">
                        <p class="text-sm text-danger-700">
                            <i class="size-4 inline" data-lucide="alert-triangle"></i>
                            <strong>Outdated purchases:</strong> These purchases have draw dates more than 4 days before the latest results. 
                            They may need manual review or the draw results may be missing from the system.
                        </p>
                    </div>
                    <div class="space-y-3">
                        @foreach($statusGroups['outdated'] as $purchase)
                            <div class="flex items-center justify-between p-4 bg-danger/5 border border-danger/20 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-lg">{{ $purchase->order_number }}</span>
                                        <span class="inline-flex px-2 py-0.5 bg-danger/20 text-danger rounded text-xs font-medium">
                                            Outdated
                                        </span>
                                    </div>
                                    <div class="text-sm text-default-600 mt-1">
                                        👤 {{ $purchase->customer->full_name ?? 'N/A' }}
                                    </div>
                                    @if($purchase->lotteryTicket && $purchase->lotteryTicket->withdraw_date)
                                        <div class="mt-2 text-xs text-danger-600">
                                            ⚠️ Ticket draw was {{ \Carbon\Carbon::parse($purchase->lotteryTicket->withdraw_date)->format('M d, Y') }}
                                            ({{ $purchase->status_info['days_difference'] ?? '?' }} days overdue)
                                        </div>
                                    @else
                                        <div class="mt-2 text-xs text-danger-600">
                                            ⚠️ Missing ticket information
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" 
                                       class="btn btn-sm bg-danger/10 text-danger hover:bg-danger/20">
                                        <i class="size-4" data-lucide="eye"></i>
                                        Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="size-16 mx-auto mb-4 text-success-300" data-lucide="check-circle"></i>
                        <p class="text-default-500">No outdated purchases! All tickets are up to date.</p>
                    </div>
                @endif
            </div>

            {{-- Recent Winners Tab --}}
            <div id="winners" class="hidden">
                @if($recentWinners->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentWinners as $winner)
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-lg">{{ $winner->order_number }}</span>
                                        <span class="inline-flex px-2 py-1 bg-purple-200 text-purple-700 rounded text-xs font-bold">
                                            {{ $winner->prize_won }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-default-600 mt-1">
                                        👤 {{ $winner->customer->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-default-500 mt-1">
                                        🎫 {{ $winner->lotteryTicket->ticket_name ?? '-' }}
                                    </div>
                                    <div class="flex items-center gap-4 mt-2 text-xs">
                                        @if($winner->drawResult)
                                            <div>
                                                <span class="text-default-500">Draw Date:</span>
                                                <span class="font-medium">{{ $winner->drawResult->date_en }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="text-default-500">Checked:</span>
                                            <span class="font-medium">{{ $winner->checked_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('purchases.show', $winner->id) }}" 
                                       class="btn btn-sm bg-purple-100 text-purple-700 hover:bg-purple-200">
                                        <i class="size-4" data-lucide="eye"></i>
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="size-16 mx-auto mb-4 text-default-300" data-lucide="trophy"></i>
                        <p class="text-default-500">No winners yet. Check purchases to find winners!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- How It Works Instructions --}}
    <div class="card">
        <div class="card-body">
            <h6 class="font-semibold mb-4 flex items-center gap-2">
                <i class="size-5 text-primary" data-lucide="info"></i>
                How the Lottery Result Checking Works
            </h6>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h6 class="font-medium text-sm mb-2 text-primary">✅ What Happens When You Check:</h6>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-default-700">
                        <li>System fetches the latest draw results from the database</li>
                        <li>Compares each approved purchase's ticket draw date with actual draw date</li>
                        <li>Accounts for draw postponements (up to 4 days for holidays)</li>
                        <li>Matches ticket numbers against all prize tiers and running numbers</li>
                        <li>Updates purchase status to "Won" or "Not Won"</li>
                        <li>Sends notifications to customers after results are checked</li>
                    </ol>
                </div>
                
                <div>
                    <h6 class="font-medium text-sm mb-2 text-warning">📅 Draw Date Matching Rules:</h6>
                    <ul class="list-disc list-inside space-y-2 text-sm text-default-700">
                        <li><strong>Exact Match:</strong> Ticket draw date = Actual draw date ✅</li>
                        <li><strong>Postponed 1-4 days:</strong> Acceptable for Thai holidays ✅</li>
                        <li><strong>Future Draw:</strong> Results not available yet, skipped ⏰</li>
                        <li><strong>More than 4 days old:</strong> Flagged as outdated ⚠️</li>
                    </ul>
                    
                    <div class="mt-4 p-3 bg-info/10 border border-info/20 rounded">
                        <p class="text-xs text-info-700">
                            <strong>Example:</strong> If ticket is for Nov 16 and draw happens Nov 18 (2-day holiday postponement), 
                            the system will still match and check this ticket correctly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection