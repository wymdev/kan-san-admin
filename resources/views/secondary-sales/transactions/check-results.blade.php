@extends('layouts.vertical', ['title' => 'Check Results'])

@section('css')
<style>
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .dark .stat-card {
        background: rgb(31, 41, 55);
        border-color: rgb(55, 65, 81);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .dark .stat-label {
        color: #9ca3af;
    }
    .module-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .module-card {
        background: rgb(31, 41, 55);
        border-color: rgb(55, 65, 81);
    }
    .module-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dark .module-header {
        border-color: rgb(55, 65, 81);
    }
    .module-body {
        padding: 1.25rem;
    }
    .action-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        border: 1px solid rgba(99, 102, 241, 0.15);
        border-radius: 16px;
        padding: 1.5rem;
    }
    .dark .action-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
        border-color: rgba(99, 102, 241, 0.25);
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }
    .alert-box {
        border-radius: 12px;
        padding: 0.875rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    @media (max-width: 640px) {
        .stat-value {
            font-size: 1.25rem;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Check Lottery Results'])

    {{-- Alerts --}}
    @if ($message = Session::get('success'))
        <div class="alert-box bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 mb-4">
            <i class="size-5" data-lucide="check-circle"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('warning'))
        <div class="alert-box bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 mb-4">
            <i class="size-5" data-lucide="alert-triangle"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('error'))
        <div class="alert-box bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 mb-4">
            <i class="size-5" data-lucide="alert-circle"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif
    
    @if ($message = Session::get('info'))
        <div class="alert-box bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300 mb-4">
            <i class="size-5" data-lucide="info"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif

    {{-- Statistics --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-amber-100 dark:bg-amber-900/30">
                    <i class="text-amber-600 dark:text-amber-400 size-5" data-lucide="clock"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Awaiting</p>
                    <p class="stat-value text-amber-600">{{ $stats['awaiting_check'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-violet-100 dark:bg-violet-900/30">
                    <i class="text-violet-600 dark:text-violet-400 size-5" data-lucide="trophy"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Won</p>
                    <p class="stat-value text-violet-600">{{ $stats['won'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-gray-100 dark:bg-gray-700">
                    <i class="text-gray-600 dark:text-gray-400 size-5" data-lucide="x-circle"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Not Won</p>
                    <p class="stat-value text-gray-600 dark:text-gray-300">{{ $stats['not_won'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-cyan-100 dark:bg-cyan-900/30">
                    <i class="text-cyan-600 dark:text-cyan-400 size-5" data-lucide="calendar-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Latest Draw</p>
                    <p class="stat-value text-gray-900 dark:text-white text-base">{{ $stats['latest_draw_date'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Check All Button --}}
    <div class="action-card mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-1">
                    <i class="size-5 text-indigo-600" data-lucide="target"></i> Check Results
                </h6>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>{{ $statusGroups['ready_to_check']->count() }}</strong> pending | 
                    <strong>{{ $statusGroups['previously_checked']->count() }}</strong> previously checked
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if($statusGroups['ready_to_check']->count() > 0)
                    <form action="{{ route('secondary-transactions.check-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn bg-primary text-white rounded-lg flex items-center gap-2 w-full sm:w-auto">
                            <i class="size-5" data-lucide="search"></i> Check Pending ({{ $statusGroups['ready_to_check']->count() }})
                        </button>
                    </form>
                @endif
                
                @if($statusGroups['can_recheck_all'])
                    <form action="{{ route('secondary-transactions.recheck-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white rounded-lg flex items-center gap-2 w-full sm:w-auto">
                            <i class="size-5" data-lucide="refresh-cw"></i> Recheck All ({{ $statusGroups['previously_checked']->count() }})
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- Ready to Check --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-emerald-500" data-lucide="check-circle"></i> Ready to Check
                </h6>
                <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">{{ $readyToCheck->count() }}</span>
            </div>
            @if($readyToCheck->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                <th class="px-4 py-3 text-left">Ticket</th>
                                <th class="px-4 py-3 text-left">Customer</th>
                                <th class="px-4 py-3 text-left">Draw</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($readyToCheck as $transaction)
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-bold text-primary">{{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $transaction->customer_display_name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $transaction->secondaryTicket?->withdraw_date?->format('M d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $readyToCheck->links() }}
                </div>
            @else
                <div class="module-body text-center py-8">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="size-7 text-emerald-600" data-lucide="check-circle"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No transactions ready to check</p>
                </div>
            @endif
        </div>

        {{-- Waiting for Draw --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-amber-500" data-lucide="hourglass"></i> Waiting for Future Draw
                </h6>
                <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">{{ $statusGroups['waiting_for_draw']->count() }}</span>
            </div>
            @if($statusGroups['waiting_for_draw']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                <th class="px-4 py-3 text-left">Ticket</th>
                                <th class="px-4 py-3 text-left">Customer</th>
                                <th class="px-4 py-3 text-left">Draw Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($statusGroups['waiting_for_draw']->take(10) as $transaction)
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-bold text-amber-600 dark:text-amber-400">{{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $transaction->customer_display_name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $transaction->secondaryTicket?->withdraw_date?->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="module-body text-center py-8">
                    <div class="w-14 h-14 bg-cyan-100 dark:bg-cyan-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="size-7 text-cyan-600" data-lucide="check"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No transactions waiting</p>
                </div>
            @endif
        </div>

        {{-- Previously Checked --}}\
        @if($previouslyChecked->total() > 0)
            <div class="module-card lg:col-span-2">
                <div class="module-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-amber-500" data-lucide="history"></i> Previously Checked
                    </h6>
                    <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">{{ $previouslyChecked->total() }}</span>
                </div>
                
                <div class="module-body">
                    {{-- Search and Filter Form --}}
                    <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by ticket, customer, phone..." 
                                   class="form-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        </div>
                        <select name="status_filter" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">All Status</option>
                            <option value="won" {{ request('status_filter') == 'won' ? 'selected' : '' }}>Won Only</option>
                            <option value="not_won" {{ request('status_filter') == 'not_won' ? 'selected' : '' }}>Not Won Only</option>
                        </select>
                        <button type="submit" class="btn bg-primary text-white rounded-lg flex items-center gap-2 px-4">
                            <i class="size-4" data-lucide="search"></i> Search
                        </button>
                        @if(request('search') || request('status_filter'))
                            <a href="{{ route('secondary-transactions.check-results') }}" 
                               class="btn bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg flex items-center gap-2 px-4">
                                <i class="size-4" data-lucide="x"></i> Clear
                            </a>
                        @endif
                    </form>

                    {{-- Recheck All Button --}}
                    <form action="{{ route('secondary-transactions.recheck-all') }}" method="POST" class="mb-4">
                        @csrf
                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white rounded-lg flex items-center gap-2">
                            <i class="size-5" data-lucide="refresh-cw"></i> Recheck All ({{ $previouslyChecked->total() }})
                        </button>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    <th class="px-4 py-3 text-left">Ticket</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-left">Current Result</th>
                                    <th class="px-4 py-3 text-left">Draw Date</th>
                                    <th class="px-4 py-3 text-left">Checked</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($previouslyChecked as $transaction)
                                    <tr class="text-sm hover:bg-amber-50 dark:hover:bg-amber-900/10">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('secondary-transactions.show', $transaction) }}" class="font-mono font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                                {{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $transaction->customer_display_name }}</td>
                                        <td class="px-4 py-3">
                                            @if($transaction->status == 'won')
                                                <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 font-bold">
                                                    🎉 {{ $transaction->prize_won }}
                                                </span>
                                            @elseif($transaction->status == 'not_won')
                                                <span class="text-gray-500">Not Won</span>
                                            @else
                                                <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $transaction->drawResult?->date_en ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $transaction->checked_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <form action="{{ route('secondary-transactions.recheck-selected') }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="transaction_ids[]" value="{{ $transaction->id }}">
                                                    <button type="submit" class="action-btn bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40" 
                                                            title="Recheck this transaction">
                                                        <i class="size-4" data-lucide="refresh-cw"></i>
                                                    </button>
                                                </form>
                                            <a href="{{ route('secondary-transactions.show', $transaction) }}" 
                                               class="action-btn bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40" 
                                               title="View details">
                                                <i class="size-4" data-lucide="eye"></i>
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No transactions found matching your search.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($previouslyChecked->hasPages())
                        <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                            {{ $previouslyChecked->appends(request()->except('checked_page'))->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Recent Winners --}}
    @if($recentWinners->count() > 0)
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-violet-500" data-lucide="trophy"></i> Recent Winners
                </h6>
                <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">{{ $recentWinners->count() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            <th class="px-4 py-3 text-left">Ticket</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Prize</th>
                            <th class="px-4 py-3 text-left">Draw Date</th>
                            <th class="px-4 py-3 text-left">Checked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentWinners as $winner)
                            <tr class="text-sm hover:bg-violet-50 dark:hover:bg-violet-900/10">
                                <td class="px-4 py-3">
                                    <a href="{{ route('secondary-transactions.show', $winner) }}" class="font-mono font-bold text-violet-600 dark:text-violet-400 hover:underline">
                                        {{ $winner->secondaryTicket?->ticket_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $winner->customer_display_name }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 font-bold">
                                        🎉 {{ $winner->prize_won }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $winner->drawResult?->date_en }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $winner->checked_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
     @endif
@endsection


