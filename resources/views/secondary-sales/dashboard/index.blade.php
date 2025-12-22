@extends('layouts.vertical', ['title' => 'Secondary Sales Dashboard'])

@section('css')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .top-buyer-card {
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--success-rgb), 0.1) 100%);
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Dashboard'])

    {{-- Date Range Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label text-sm">From:</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label text-sm">To:</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input">
                </div>
                <button type="submit" class="btn bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="filter"></i> Apply Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Overview Stats --}}
    <div class="grid xl:grid-cols-5 lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 mb-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-primary/10 rounded-lg">
                        <i class="text-primary size-6" data-lucide="receipt"></i>
                    </div>
                    <div>
                        <p class="text-xs text-default-500">Transactions</p>
                        <h4 class="text-2xl font-bold">{{ $revenueStats['transaction_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-success/10 rounded-lg">
                        <i class="text-success size-6" data-lucide="banknote"></i>
                    </div>
                    <div>
                        <p class="text-xs text-default-500">Period Revenue</p>
                        <h4 class="text-xl font-bold">฿{{ number_format($revenueStats['period_revenue'], 0) }}</h4>
                        @if($revenueStats['revenue_change'] != 0)
                            <span class="text-xs {{ $revenueStats['revenue_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $revenueStats['revenue_change'] > 0 ? '+' : '' }}{{ $revenueStats['revenue_change'] }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="text-purple-600 size-6" data-lucide="trophy"></i>
                    </div>
                    <div>
                        <p class="text-xs text-default-500">Winners</p>
                        <h4 class="text-2xl font-bold">{{ $winLossStats['won'] }}</h4>
                        <span class="text-xs text-success">{{ $winLossStats['win_rate'] }}% win rate</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-danger/10 rounded-lg">
                        <i class="text-danger size-6" data-lucide="alert-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs text-default-500">Unpaid</p>
                        <h4 class="text-xl font-bold">฿{{ number_format($paymentStats['total_pending'], 0) }}</h4>
                        <span class="text-xs text-default-500">{{ $paymentStats['unpaid_count'] }} transactions</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-info/10 rounded-lg">
                        <i class="text-info size-6" data-lucide="ticket"></i>
                    </div>
                    <div>
                        <p class="text-xs text-default-500">Tickets</p>
                        <h4 class="text-2xl font-bold">{{ $ticketStats['total_tickets'] }}</h4>
                        <span class="text-xs text-default-500">{{ $ticketStats['tickets_sold'] }} sold</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- Top Buyers --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="trophy"></i> Top Buyers
                </h6>
            </div>
            <div class="card-body">
                @forelse($topBuyers as $index => $buyer)
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-default-100' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full {{ $index < 3 ? 'bg-primary text-white' : 'bg-default-100 text-default-600' }} font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $buyer['name'] }}</p>
                                <p class="text-xs text-default-500">
                                    {{ $buyer['transaction_count'] }} txn
                                    @if($buyer['wins'] > 0)
                                        <span class="text-purple-600 ml-1"><i class="size-3 inline" data-lucide="award"></i> {{ $buyer['wins'] }} wins</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-success">฿{{ number_format($buyer['total_spent'], 0) }}</p>
                            @if(!$buyer['is_registered'])
                                <span class="text-xs text-warning">Guest</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-default-500 py-4">No buyers in this period</p>
                @endforelse
            </div>
        </div>

        {{-- Win/Loss Breakdown --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="bar-chart-3"></i> Win/Loss Breakdown
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <h5 class="text-2xl font-bold text-purple-700">{{ $winLossStats['won'] }}</h5>
                        <p class="text-sm text-purple-600">Won</p>
                    </div>
                    <div class="text-center p-4 bg-gray-100 rounded-lg">
                        <h5 class="text-2xl font-bold text-gray-700">{{ $winLossStats['not_won'] }}</h5>
                        <p class="text-sm text-gray-600">Not Won</p>
                    </div>
                    <div class="text-center p-4 bg-warning/10 rounded-lg">
                        <h5 class="text-2xl font-bold text-warning">{{ $winLossStats['pending_check'] }}</h5>
                        <p class="text-sm text-warning">Pending</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Win Rate</span>
                        <span class="font-bold">{{ $winLossStats['win_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-purple-600 h-3 rounded-full" style="width: {{ min($winLossStats['win_rate'], 100) }}%"></div>
                    </div>
                </div>

                @if($winLossStats['pending_check'] > 0)
                    <a href="{{ route('secondary-transactions.check-results') }}" class="btn bg-primary text-white w-full">
                        <i class="size-4 me-1" data-lucide="search"></i> Check Results
                    </a>
                @endif
            </div>
        </div>

        {{-- Payment Status --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="banknote"></i> Payment Collection
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="text-center p-4 bg-success/10 rounded-lg">
                        <h5 class="text-xl font-bold text-success">฿{{ number_format($paymentStats['total_collected'], 0) }}</h5>
                        <p class="text-sm text-success">Collected</p>
                        <p class="text-xs text-default-500">{{ $paymentStats['paid_count'] }} txn</p>
                    </div>
                    <div class="text-center p-4 bg-danger/10 rounded-lg">
                        <h5 class="text-xl font-bold text-danger">฿{{ number_format($paymentStats['total_pending'], 0) }}</h5>
                        <p class="text-sm text-danger">Pending</p>
                        <p class="text-xs text-default-500">{{ $paymentStats['unpaid_count'] }} txn</p>
                    </div>
                </div>

                @php
                    $totalPayments = $paymentStats['total_collected'] + $paymentStats['total_pending'];
                    $collectionRate = $totalPayments > 0 ? ($paymentStats['total_collected'] / $totalPayments) * 100 : 0;
                @endphp

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Collection Rate</span>
                        <span class="font-bold">{{ number_format($collectionRate, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-success h-3 rounded-full" style="width: {{ min($collectionRate, 100) }}%"></div>
                    </div>
                </div>

                @if($paymentStats['unpaid_count'] > 0)
                    <a href="{{ route('secondary-transactions.index', ['is_paid' => 'no']) }}" class="btn bg-danger/10 text-danger w-full">
                        View Unpaid ({{ $paymentStats['unpaid_count'] }})
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="list"></i> Recent Transactions
                </h6>
                <a href="{{ route('secondary-transactions.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-default-200">
                    <thead class="bg-default-150">
                    <tr class="text-sm font-normal text-default-700">
                        <th class="px-3.5 py-3 text-start">Ticket</th>
                        <th class="px-3.5 py-3 text-start">Customer</th>
                        <th class="px-3.5 py-3 text-start">Amount</th>
                        <th class="px-3.5 py-3 text-start">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr class="text-sm hover:bg-default-50">
                            <td class="px-3.5 py-3 font-mono text-primary">
                                <a href="{{ route('secondary-transactions.show', $transaction) }}" class="hover:underline">
                                    {{ $transaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-3.5 py-3 text-xs">{{ Str::limit($transaction->customer_display_name, 15) }}</td>
                            <td class="px-3.5 py-3 font-semibold">฿{{ number_format($transaction->amount, 0) }}</td>
                            <td class="px-3.5 py-3">
                                @if($transaction->status == 'won')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-medium">
                                        <i class="size-3" data-lucide="trophy"></i> Won
                                    </span>
                                @elseif($transaction->status == 'not_won')
                                    <span class="text-gray-500 text-xs">Not Won</span>
                                @else
                                    <span class="text-warning text-xs">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3.5 py-8 text-center text-default-500">No transactions yet</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Winners --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title flex items-center gap-2">
                    <i class="size-4" data-lucide="award"></i> Recent Winners
                </h6>
            </div>
            <div class="card-body">
                @forelse($recentWinners as $winner)
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-default-100' : '' }}">
                        <div>
                            <span class="font-mono font-bold text-purple-600">{{ $winner->secondaryTicket?->ticket_number }}</span>
                            <p class="text-xs text-default-500">{{ $winner->customer_display_name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">
                                {{ $winner->prize_won }}
                            </span>
                            <p class="text-xs text-default-400 mt-1">{{ $winner->checked_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-default-500 py-8">
                        <i class="size-10 mx-auto mb-2 text-default-300" data-lucide="trophy"></i>
                        <p>No winners yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
