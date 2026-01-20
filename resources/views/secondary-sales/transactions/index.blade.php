@extends('layouts.vertical', ['title' => 'Secondary Transactions'])

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
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .dark .filter-card {
        background: linear-gradient(135deg, rgb(31, 41, 55) 0%, rgb(17, 24, 39) 100%);
    }
    .filter-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    @media (max-width: 640px) {
        .filter-section {
            grid-template-columns: 1fr 1fr;
        }
        .filter-section .full-width {
            grid-column: span 2;
        }
        .stat-value {
            font-size: 1.25rem;
        }
    }
    .table-container {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .table-container {
        border-color: rgb(55, 65, 81);
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Transaction Management'])

    {{-- Alert Messages --}}
    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <i class="size-5" data-lucide="check-circle"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif
    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <i class="size-5" data-lucide="alert-circle"></i>
            <span>{{ $message }}</span>
        </div>
    @endif
    @if ($message = Session::get('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <i class="size-5" data-lucide="alert-triangle"></i>
            <span>{!! $message !!}</span>
        </div>
    @endif

    {{-- Filter Card for Stats --}}
    @if(isset($stats))
    <div class="filter-card">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Overview Statistics</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Transaction summary and analytics</p>
            </div>
            <form method="GET" action="{{ route('secondary-transactions.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="is_paid" value="{{ request('is_paid') }}">
                <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700">
                    <i class="size-4 text-gray-400" data-lucide="calendar"></i>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-transparent border-none text-sm focus:outline-none w-32" placeholder="From">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-transparent border-none text-sm focus:outline-none w-32" placeholder="To">
                </div>
                <button type="submit" class="btn btn-sm bg-primary text-white rounded-lg">
                    <i class="size-4" data-lucide="filter"></i>
                </button>
                @if(request()->hasAny(['date_from', 'date_to']))
                    <a href="{{ route('secondary-transactions.index', request()->except(['date_from', 'date_to'])) }}" class="btn btn-sm bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                        <i class="size-4" data-lucide="x"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/30">
                        <i class="text-indigo-600 dark:text-indigo-400 size-5" data-lucide="receipt"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Total</p>
                        <p class="stat-value text-gray-900 dark:text-white">{{ $stats['total_transactions'] }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-amber-100 dark:bg-amber-900/30">
                        <i class="text-amber-600 dark:text-amber-400 size-5" data-lucide="clock"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Unchecked</p>
                        <p class="stat-value text-gray-900 dark:text-white">{{ $stats['awaiting_check'] }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                        <i class="text-emerald-600 dark:text-emerald-400 size-5" data-lucide="trophy"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Won</p>
                        <p class="stat-value text-emerald-600 dark:text-emerald-400">{{ $stats['won'] }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-rose-100 dark:bg-rose-900/30">
                        <i class="text-rose-600 dark:text-rose-400 size-5" data-lucide="circle-dollar-sign"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Unpaid</p>
                        <p class="stat-value text-rose-600 dark:text-rose-400">฿{{ number_format($stats['unpaid_amount'], 0) }}</p>
                        <p class="text-xs text-rose-500">{{ number_format($stats['unpaid_amount_mmk'] ?? 0, 0) }} K</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-cyan-100 dark:bg-cyan-900/30">
                        <i class="text-cyan-600 dark:text-cyan-400 size-5" data-lucide="wallet"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Revenue THB</p>
                        <p class="stat-value text-gray-900 dark:text-white">฿{{ number_format($stats['total_revenue'], 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-purple-100 dark:bg-purple-900/30">
                        <i class="text-purple-600 dark:text-purple-400 size-5" data-lucide="banknote"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="stat-label">Revenue MMK</p>
                        <p class="stat-value text-gray-900 dark:text-white">{{ number_format($stats['total_revenue_mmk'] ?? 0, 0) }}K</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Table Card --}}
    <div class="table-container bg-white dark:bg-gray-800">
        {{-- Table Header --}}
        <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h6 class="text-lg font-semibold text-gray-900 dark:text-white">Transactions</h6>
                    <span class="badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $transactions->total() }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('secondary-transactions.create') }}" class="btn btn-sm bg-primary text-white rounded-lg inline-flex items-center gap-1.5">
                        <i class="size-4" data-lucide="plus"></i>
                        <span class="hidden sm:inline">New Transaction</span>
                    </a>
                    <a href="{{ route('secondary-transactions.check-results') }}" class="btn btn-sm bg-info text-white rounded-lg inline-flex items-center gap-1.5">
                        <i class="size-4" data-lucide="search"></i>
                        <span class="hidden sm:inline">Check Results</span>
                    </a>
                    <a href="{{ route('secondary-transactions.export', request()->query()) }}" class="btn btn-sm bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg inline-flex items-center gap-1.5">
                        <i class="size-4" data-lucide="download"></i>
                        <span class="hidden sm:inline">Export</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('secondary-transactions.index') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <div class="filter-section">
                    <div class="full-width">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="form-input form-input-sm w-full pl-9 rounded-lg" placeholder="Search transactions...">
                            <i class="size-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" data-lucide="search"></i>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status</label>
                        <select name="status" class="form-select form-select-sm w-full rounded-lg">
                            <option value="">All</option>
                            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="won" {{ request('status')=='won' ? 'selected' : '' }}>Won</option>
                            <option value="not_won" {{ request('status')=='not_won' ? 'selected' : '' }}>Not Won</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Payment</label>
                        <select name="is_paid" class="form-select form-select-sm w-full rounded-lg">
                            <option value="">All</option>
                            <option value="yes" {{ request('is_paid')=='yes' ? 'selected' : '' }}>Paid</option>
                            <option value="no" {{ request('is_paid')=='no' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Method</label>
                        <select name="payment_method" class="form-select form-select-sm w-full rounded-lg">
                            <option value="">All</option>
                            <option value="Cash" {{ request('payment_method')=='Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank Transfer" {{ request('payment_method')=='Bank Transfer' ? 'selected' : '' }}>Bank</option>
                            <option value="PromptPay" {{ request('payment_method')=='PromptPay' ? 'selected' : '' }}>PromptPay</option>
                            <option value="KBZPay" {{ request('payment_method')=='KBZPay' ? 'selected' : '' }}>KBZPay</option>
                            <option value="WavePay" {{ request('payment_method')=='WavePay' ? 'selected' : '' }}>WavePay</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-sm bg-primary text-white rounded-lg flex-1">
                            <i class="size-4" data-lucide="filter"></i>
                        </button>
                        @if(request()->hasAny(['search', 'status', 'is_paid', 'payment_method']))
                            <a href="{{ route('secondary-transactions.index', request()->only(['date_from', 'date_to'])) }}" class="btn btn-sm bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                                <i class="size-4" data-lucide="x"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Transaction</th>
                        <th class="px-4 py-3 text-left">Lottery</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($transactions as $i => $transaction)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $transactions->firstItem() + $i }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ $transaction->transaction_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-mono font-bold text-primary text-sm">
                                    {{ $transaction->secondaryTicket?->ticket_number ?? '-' }}
                                </div>
                                @if($transaction->secondaryTicket?->withdraw_date)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        🎯 {{ $transaction->secondaryTicket->withdraw_date->format('M d') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->customer_display_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->customer_display_phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900 dark:text-white">฿{{ number_format($transaction->amount_thb, 2) }}</div>
                                @if($transaction->amount_mmk)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($transaction->amount_mmk, 2) }} K</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($transaction->status == 'won')
                                    <span class="badge bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">🎉 Won</span>
                                    @if($transaction->prize_won)
                                        <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">{{ $transaction->prize_won }}</div>
                                    @endif
                                @elseif($transaction->status == 'not_won')
                                    <span class="badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Not Won</span>
                                @else
                                    <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($transaction->is_paid)
                                    <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                        ✓ {{ $transaction->payment_method ?? 'Paid' }}
                                    </span>
                                @else
                                    <span class="badge bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $transaction->purchased_at->format('M d, Y') }}<br>
                                <span class="text-gray-400">{{ $transaction->purchased_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('secondary-transactions.show', $transaction) }}" class="action-btn bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-100 dark:hover:bg-cyan-900/40">
                                        <i class="size-4" data-lucide="eye"></i>
                                    </a>
                                    <a href="{{ route('secondary-transactions.edit', $transaction) }}" class="action-btn bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40">
                                        <i class="size-4" data-lucide="edit"></i>
                                    </a>
                                    @if(!$transaction->is_paid)
                                        <button type="button" onclick="showPayModal('{{ $transaction->id }}')" class="action-btn bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40">
                                            <i class="size-4" data-lucide="wallet"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="size-8 text-gray-400" data-lucide="inbox"></i>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 mb-2">No transactions found</p>
                                    <a href="{{ route('secondary-transactions.create') }}" class="text-primary hover:underline text-sm">
                                        Create your first transaction →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium">{{ $transactions->firstItem() }}</span> to <span class="font-medium">{{ $transactions->lastItem() }}</span> of <span class="font-medium">{{ $transactions->total() }}</span> results
            </p>
            <nav class="flex items-center gap-1">
                {{ $transactions->links() }}
            </nav>
        </div>
        @endif
    </div>

    {{-- Mark Paid Modal --}}
    <div id="payModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="hidePayModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 transform transition-all">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <i class="size-6 text-emerald-600 dark:text-emerald-400" data-lucide="wallet"></i>
                    </div>
                    <div>
                        <h5 class="text-lg font-bold text-gray-900 dark:text-white">Mark as Paid</h5>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Confirm payment for this transaction</p>
                    </div>
                </div>
                <form id="payForm" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                        <select name="payment_method" class="form-select w-full rounded-xl" required>
                            <option value="Cash">💵 Cash</option>
                            <option value="Bank Transfer">🏦 Bank Transfer</option>
                            <option value="PromptPay">📱 PromptPay</option>
                            <option value="KBZPay">📱 KBZPay</option>
                            <option value="WavePay">📱 WavePay</option>
                            <option value="Other">📋 Other</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 btn bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2.5">
                            Confirm Payment
                        </button>
                        <button type="button" class="btn bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl px-6" onclick="hidePayModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
function showPayModal(transactionId) {
    document.getElementById('payForm').action = `/secondary-transactions/${transactionId}/mark-paid`;
    document.getElementById('payModal').classList.remove('hidden');
}
function hidePayModal() {
    document.getElementById('payModal').classList.add('hidden');
}
</script>
@endsection
