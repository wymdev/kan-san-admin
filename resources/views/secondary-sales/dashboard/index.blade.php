@extends('layouts.vertical', ['title' => 'Secondary Sales Dashboard'])

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
    .filter-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
    }
    .dark .filter-card {
        background: linear-gradient(135deg, rgb(31, 41, 55) 0%, rgb(17, 24, 39) 100%);
        border-color: rgb(55, 65, 81);
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
    .progress-bar-container {
        height: 8px;
        background: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }
    .dark .progress-bar-container {
        background: rgb(55, 65, 81);
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.5s ease;
    }
    .buyer-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
    }
    .buyer-item:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }
    .dark .buyer-item:not(:last-child) {
        border-bottom-color: rgb(55, 65, 81);
    }
    .rank-badge {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }
    .stat-box {
        text-align: center;
        padding: 1rem;
        border-radius: 12px;
    }
    @media (max-width: 640px) {
        .stat-value {
            font-size: 1.125rem;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Dashboard'])

    {{-- Date Range Filter --}}
    <div class="filter-card mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full sm:w-auto">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" 
                    class="form-input w-full rounded-lg">
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" 
                    class="form-input w-full rounded-lg">
            </div>
            <button type="submit" class="btn bg-primary text-white rounded-lg w-full sm:w-auto flex items-center justify-center gap-2">
                <i class="size-4" data-lucide="filter"></i> Apply
            </button>
            @if(request('date_from') || request('date_to'))
                <a href="{{ route('secondary-sales.dashboard') }}" class="btn bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                    <i class="size-4" data-lucide="x"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Overview Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/30">
                    <i class="text-indigo-600 dark:text-indigo-400 size-5" data-lucide="receipt"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Transactions</p>
                    <p class="stat-value text-gray-900 dark:text-white">{{ number_format($revenueStats['transaction_count']) }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                    <i class="text-emerald-600 dark:text-emerald-400 size-5" data-lucide="banknote"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Revenue</p>
                    <p class="stat-value text-emerald-600">฿{{ number_format($revenueStats['period_revenue'], 0) }}</p>
                    <p class="stat-subvalue text-emerald-500">{{ number_format($revenueStats['period_revenue_mmk'] ?? 0, 0) }} K</p>
                    @if($revenueStats['revenue_change'] != 0)
                        <span class="text-xs {{ $revenueStats['revenue_change'] > 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $revenueStats['revenue_change'] > 0 ? '+' : '' }}{{ $revenueStats['revenue_change'] }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-violet-100 dark:bg-violet-900/30">
                    <i class="text-violet-600 dark:text-violet-400 size-5" data-lucide="trophy"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Winners</p>
                    <p class="stat-value text-violet-600">{{ $winLossStats['won'] }}</p>
                    <span class="text-xs text-emerald-500">{{ $winLossStats['win_rate'] }}% rate</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-rose-100 dark:bg-rose-900/30">
                    <i class="text-rose-600 dark:text-rose-400 size-5" data-lucide="alert-circle"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Unpaid</p>
                    <p class="stat-value text-rose-600">฿{{ number_format($paymentStats['total_pending'], 0) }}</p>
                    <p class="stat-subvalue text-rose-500">{{ number_format($paymentStats['total_pending_mmk'] ?? 0, 0) }} K</p>
                    <span class="text-xs text-gray-500">{{ $paymentStats['unpaid_count'] }} txn</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-cyan-100 dark:bg-cyan-900/30">
                    <i class="text-cyan-600 dark:text-cyan-400 size-5" data-lucide="ticket"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Tickets</p>
                    <p class="stat-value text-gray-900 dark:text-white">{{ $ticketStats['total_tickets'] }}</p>
                    <span class="text-xs text-emerald-500">{{ $ticketStats['tickets_sold'] }} sold</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- Top Buyers --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-amber-500" data-lucide="trophy"></i> Top Buyers
                </h6>
                <span class="badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ count($topBuyers) }}</span>
            </div>
            <div class="module-body">
                @forelse($topBuyers as $index => $buyer)
                    <div class="buyer-item">
                        <div class="flex items-center gap-3">
                            <div class="rank-badge {{ $index < 3 ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $buyer['name'] }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $buyer['transaction_count'] }} txn</span>
                                    @if($buyer['wins'] > 0)
                                        <span class="text-violet-600 dark:text-violet-400 flex items-center gap-0.5">
                                            <i class="size-3" data-lucide="award"></i> {{ $buyer['wins'] }} wins
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-emerald-600">฿{{ number_format($buyer['total_spent'], 0) }}</p>
                            @if(!$buyer['is_registered'])
                                <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px]">Guest</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-8 text-gray-500">
                        <i class="size-12 text-gray-300 mb-2" data-lucide="users"></i>
                        <p>No buyers in this period</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Win/Loss Breakdown --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-violet-500" data-lucide="bar-chart-3"></i> Win/Loss Breakdown
                </h6>
            </div>
            <div class="module-body">
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="stat-box bg-violet-50 dark:bg-violet-900/20">
                        <h5 class="text-2xl font-bold text-violet-700 dark:text-violet-400">{{ $winLossStats['won'] }}</h5>
                        <p class="text-sm text-violet-600 dark:text-violet-400">Won</p>
                    </div>
                    <div class="stat-box bg-gray-100 dark:bg-gray-700">
                        <h5 class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $winLossStats['not_won'] }}</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Not Won</p>
                    </div>
                    <div class="stat-box bg-amber-50 dark:bg-amber-900/20">
                        <h5 class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $winLossStats['pending_check'] }}</h5>
                        <p class="text-sm text-amber-600 dark:text-amber-400">Pending</p>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Win Rate</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $winLossStats['win_rate'] }}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill bg-violet-600" style="width: {{ min($winLossStats['win_rate'], 100) }}%"></div>
                    </div>
                </div>

                @if($winLossStats['pending_check'] > 0)
                    <a href="{{ route('secondary-transactions.check-results') }}" class="btn bg-primary text-white w-full rounded-lg flex items-center justify-center gap-2">
                        <i class="size-4" data-lucide="search"></i> Check Results ({{ $winLossStats['pending_check'] }})
                    </a>
                @endif
            </div>
        </div>

        {{-- Payment Status --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-emerald-500" data-lucide="banknote"></i> Payment Collection
                </h6>
            </div>
            <div class="module-body">
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="stat-box bg-emerald-50 dark:bg-emerald-900/20">
                        <h5 class="text-xl font-bold text-emerald-600">฿{{ number_format($paymentStats['total_collected'], 0) }}</h5>
                        <p class="text-sm font-semibold text-emerald-500">{{ number_format($paymentStats['total_collected_mmk'] ?? 0, 0) }} K</p>
                        <p class="text-sm text-emerald-600">Collected</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $paymentStats['paid_count'] }} txn</p>
                    </div>
                    <div class="stat-box bg-rose-50 dark:bg-rose-900/20">
                        <h5 class="text-xl font-bold text-rose-600">฿{{ number_format($paymentStats['total_pending'], 0) }}</h5>
                        <p class="text-sm font-semibold text-rose-500">{{ number_format($paymentStats['total_pending_mmk'] ?? 0, 0) }} K</p>
                        <p class="text-sm text-rose-600">Pending</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $paymentStats['unpaid_count'] }} txn</p>
                    </div>
                </div>

                @php
                    $totalPayments = $paymentStats['total_collected'] + $paymentStats['total_pending'];
                    $collectionRate = $totalPayments > 0 ? ($paymentStats['total_collected'] / $totalPayments) * 100 : 0;
                @endphp

                <div class="mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Collection Rate</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($collectionRate, 1) }}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill bg-emerald-600" style="width: {{ min($collectionRate, 100) }}%"></div>
                    </div>
                </div>

                @if($paymentStats['unpaid_count'] > 0)
                    <a href="{{ route('secondary-transactions.index', ['is_paid' => 'no']) }}" class="btn bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 w-full rounded-lg flex items-center justify-center gap-2">
                        <i class="size-4" data-lucide="alert-circle"></i> View Unpaid ({{ $paymentStats['unpaid_count'] }})
                    </a>
                @endif
            </div>
        </div>

        {{-- Ticket Stats --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-cyan-500" data-lucide="ticket"></i> Ticket Statistics
                </h6>
                <a href="{{ route('secondary-tickets.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="module-body">
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="stat-box bg-indigo-50 dark:bg-indigo-900/20">
                        <h5 class="text-2xl font-bold text-indigo-600">{{ $ticketStats['total_tickets'] }}</h5>
                        <p class="text-sm text-indigo-600">Total</p>
                    </div>
                    <div class="stat-box bg-emerald-50 dark:bg-emerald-900/20">
                        <h5 class="text-2xl font-bold text-emerald-600">{{ $ticketStats['tickets_sold'] }}</h5>
                        <p class="text-sm text-emerald-600">Sold</p>
                    </div>
                    <div class="stat-box bg-amber-50 dark:bg-amber-900/20">
                        <h5 class="text-2xl font-bold text-amber-600">{{ $ticketStats['total_tickets'] - $ticketStats['tickets_sold'] }}</h5>
                        <p class="text-sm text-amber-600">Available</p>
                    </div>
                </div>

                @php
                    $soldRate = $ticketStats['total_tickets'] > 0 ? ($ticketStats['tickets_sold'] / $ticketStats['total_tickets']) * 100 : 0;
                @endphp

                <div class="mb-5">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Sold Rate</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($soldRate, 1) }}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill bg-indigo-600" style="width: {{ min($soldRate, 100) }}%"></div>
                    </div>
                </div>

                <a href="{{ route('secondary-tickets.create') }}" class="btn bg-primary text-white w-full rounded-lg flex items-center justify-center gap-2">
                    <i class="size-4" data-lucide="plus"></i> Add New Ticket
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Tables --}}
    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Recent Transactions --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-indigo-500" data-lucide="list"></i> Recent Transactions
                </h6>
                <a href="{{ route('secondary-transactions.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            <th class="px-4 py-3 text-left">Ticket</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Amount</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentTransactions as $transaction)
                            <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('secondary-transactions.show', $transaction) }}" class="font-mono font-semibold text-primary hover:underline">
                                        {{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                    {{ Str::limit($transaction->customer_display_name, 15) }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    ฿{{ number_format($transaction->amount, 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($transaction->status == 'won')
                                        <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">
                                            <i class="size-3 mr-1" data-lucide="trophy"></i> Won
                                        </span>
                                    @elseif($transaction->status == 'not_won')
                                        <span class="text-gray-500 text-xs">Not Won</span>
                                    @else
                                        <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <i class="size-8 text-gray-300 mx-auto mb-2" data-lucide="inbox"></i>
                                    <p>No transactions yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Winners --}}
        <div class="module-card">
            <div class="module-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-amber-500" data-lucide="award"></i> Recent Winners
                </h6>
            </div>
            <div class="module-body">
                @forelse($recentWinners as $winner)
                    <div class="buyer-item">
                        <div>
                            <span class="font-mono font-bold text-violet-600 dark:text-violet-400">{{ $winner->secondaryTicket?->ticket_number }}</span>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $winner->customer_display_name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 font-bold">
                                {{ $winner->prize_won }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">{{ $winner->checked_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-8 text-gray-500">
                        <i class="size-12 text-gray-300 mb-2" data-lucide="trophy"></i>
                        <p>No winners yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
