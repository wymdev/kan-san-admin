@extends('layouts.vertical', ['title' => 'Check Results'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Check Lottery Results'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('info'))
        <div class="bg-info/10 border border-info/20 text-info px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    {{-- Statistics --}}
    <div class="grid lg:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-warning/10 rounded-lg">
                        <i class="text-warning size-6" data-lucide="clock"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Awaiting Check</p>
                        <h4 class="text-2xl font-bold">{{ $stats['awaiting_check'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-success/10 rounded-lg">
                        <i class="text-success size-6" data-lucide="trophy"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total Won</p>
                        <h4 class="text-2xl font-bold">{{ $stats['won'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <i class="text-gray-600 size-6" data-lucide="x-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Not Won</p>
                        <h4 class="text-2xl font-bold">{{ $stats['not_won'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-info/10 rounded-lg">
                        <i class="text-info size-6" data-lucide="calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Latest Draw</p>
                        <h4 class="text-lg font-bold">{{ $stats['latest_draw_date'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Check All Button --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h6 class="font-semibold flex items-center gap-2"><i class="size-4 text-primary" data-lucide="target"></i> Check All Pending Results</h6>
                    <p class="text-sm text-default-500">
                        {{ $statusGroups['ready_to_check']->count() }} transaction(s) ready to check against latest draw results.
                    </p>
                </div>
                <form action="{{ route('secondary-transactions.check-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn bg-primary text-white" {{ $statusGroups['ready_to_check']->count() === 0 ? 'disabled' : '' }}>
                        <i class="size-4 me-1" data-lucide="search"></i> Check All Results
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Ready to Check --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2"><i class="size-4 text-success" data-lucide="check-circle"></i> Ready to Check ({{ $readyToCheck->count() }})</h6>
            </div>
            @if($readyToCheck->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-default-200">
                        <thead class="bg-default-150">
                        <tr class="text-sm font-normal text-default-700">
                            <th class="px-3.5 py-3 text-start">Ticket</th>
                            <th class="px-3.5 py-3 text-start">Customer</th>
                            <th class="px-3.5 py-3 text-start">Draw Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($readyToCheck as $transaction)
                            <tr class="text-sm hover:bg-default-50">
                                <td class="px-3.5 py-3 font-mono font-bold text-primary">
                                    {{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                                </td>
                                <td class="px-3.5 py-3">{{ $transaction->customer_display_name }}</td>
                                <td class="px-3.5 py-3 text-xs">{{ $transaction->secondaryTicket?->withdraw_date?->format('M d') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $readyToCheck->links() }}
                </div>
            @else
                <div class="card-body text-center text-default-500">
                    <i class="size-8 text-success mx-auto mb-2" data-lucide="check-circle"></i>
                    <p>No transactions ready to check</p>
                </div>
            @endif
        </div>

        {{-- Waiting for Draw --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2"><i class="size-4 text-warning" data-lucide="hourglass"></i> Waiting for Future Draw ({{ $statusGroups['waiting_for_draw']->count() }})</h6>
            </div>
            @if($statusGroups['waiting_for_draw']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-default-200">
                        <thead class="bg-default-150">
                        <tr class="text-sm font-normal text-default-700">
                            <th class="px-3.5 py-3 text-start">Ticket</th>
                            <th class="px-3.5 py-3 text-start">Customer</th>
                            <th class="px-3.5 py-3 text-start">Draw Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($statusGroups['waiting_for_draw']->take(10) as $transaction)
                            <tr class="text-sm hover:bg-default-50">
                                <td class="px-3.5 py-3 font-mono font-bold text-warning">
                                    {{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                                </td>
                                <td class="px-3.5 py-3">{{ $transaction->customer_display_name }}</td>
                                <td class="px-3.5 py-3 text-xs">{{ $transaction->secondaryTicket?->withdraw_date?->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body text-center text-default-500">
                    <i class="size-8 text-info mx-auto mb-2" data-lucide="check"></i>
                    <p>No transactions waiting</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Winners --}}
    @if($recentWinners->count() > 0)
        <div class="card mt-6">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2"><i class="size-4 text-purple-600" data-lucide="trophy"></i> Recent Winners</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-default-200">
                    <thead class="bg-default-150">
                    <tr class="text-sm font-normal text-default-700">
                        <th class="px-3.5 py-3 text-start">Ticket</th>
                        <th class="px-3.5 py-3 text-start">Customer</th>
                        <th class="px-3.5 py-3 text-start">Prize</th>
                        <th class="px-3.5 py-3 text-start">Draw Date</th>
                        <th class="px-3.5 py-3 text-start">Checked</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentWinners as $winner)
                        <tr class="text-sm hover:bg-purple-50">
                            <td class="px-3.5 py-3 font-mono font-bold text-purple-600">
                                {{ $winner->secondaryTicket?->ticket_number ?? 'N/A' }}
                            </td>
                            <td class="px-3.5 py-3">{{ $winner->customer_display_name }}</td>
                            <td class="px-3.5 py-3">
                                <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">
                                    {{ $winner->prize_won }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3 text-xs">{{ $winner->drawResult?->date_en }}</td>
                            <td class="px-3.5 py-3 text-xs">{{ $winner->checked_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
