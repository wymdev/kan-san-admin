@extends('layouts.vertical', ['title' => 'Secondary Transactions'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Transaction Management'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    @if ($message = Session::get('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $message !!}</span>
        </div>
    @endif

    {{-- Statistics Cards --}}
    @if(isset($stats))
    <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-2 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-primary/10 rounded-lg">
                        <i class="text-primary size-6" data-lucide="receipt"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total</p>
                        <h4 class="text-2xl font-bold">{{ $stats['total_transactions'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-warning/10 rounded-lg">
                        <i class="text-warning size-6" data-lucide="clock"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Unchecked</p>
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
                        <p class="text-sm text-default-600">Won</p>
                        <h4 class="text-2xl font-bold">{{ $stats['won'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-danger/10 rounded-lg">
                        <i class="text-danger size-6" data-lucide="circle-dollar-sign"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Unpaid</p>
                        <h4 class="text-2xl font-bold">฿{{ number_format($stats['unpaid_amount'], 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-info/10 rounded-lg">
                        <i class="text-info size-6" data-lucide="wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total Revenue (THB)</p>
                        <h4 class="text-2xl font-bold">฿{{ number_format($stats['total_revenue'], 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="text-purple-600 size-6" data-lucide="banknote"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total Revenue (MMK)</p>
                        <h4 class="text-2xl font-bold">{{ number_format($stats['total_revenue_mmk'] ?? 0, 0) }} K</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('secondary-transactions.create') }}" class="btn btn-xs bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="plus"></i>New Transaction
                </a>
                <a href="{{ route('secondary-transactions.check-results') }}" class="btn btn-xs bg-info text-white">
                    <i class="size-4 me-1" data-lucide="search"></i>Check Results
                </a>
                <a href="{{ route('secondary-transactions.export', request()->query()) }}" class="btn btn-xs bg-success/10 text-success">
                    <i class="size-4 me-1" data-lucide="download"></i>Export CSV
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Transactions</h6>
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('secondary-transactions.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 12rem;">
                        <input 
                            class="ps-10 form-input form-input-sm w-full" 
                            placeholder="Search..." 
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    
                    <div style="width: 7.5rem;">
                        <select name="status" class="form-input form-input-sm w-full">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="won" {{ request('status')=='won' ? 'selected' : '' }}>Won</option>
                            <option value="not_won" {{ request('status')=='not_won' ? 'selected' : '' }}>Not Won</option>
                        </select>
                    </div>

                    <div style="width: 6.5rem;">
                        <select name="is_paid" class="form-input form-input-sm w-full">
                            <option value="">Payment</option>
                            <option value="yes" {{ request('is_paid')=='yes' ? 'selected' : '' }}>Paid</option>
                            <option value="no" {{ request('is_paid')=='no' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>

                    <div style="width: 7.5rem;">
                        <select name="payment_method" class="form-input form-input-sm w-full">
                            <option value="">Method</option>
                            <option value="Cash" {{ request('payment_method')=='Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank Transfer" {{ request('payment_method')=='Bank Transfer' ? 'selected' : '' }}>Bank</option>
                            <option value="PromptPay" {{ request('payment_method')=='PromptPay' ? 'selected' : '' }}>PromptPay</option>
                            <option value="KBZPay" {{ request('payment_method')=='KBZPay' ? 'selected' : '' }}>KBZPay</option>
                            <option value="WavePay" {{ request('payment_method')=='WavePay' ? 'selected' : '' }}>WavePay</option>
                        </select>
                    </div>

                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input form-input-sm" style="width: 8.5rem;" placeholder="From">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input form-input-sm" style="width: 8.5rem;" placeholder="To">
                    
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'is_paid', 'payment_method', 'date_from', 'date_to']))
                        <a href="{{ route('secondary-transactions.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-default-200">
                <thead class="bg-default-150">
                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                    <th class="px-3.5 py-3 text-start">No</th>
                    <th class="px-3.5 py-3 text-start">Transaction #</th>
                    <th class="px-3.5 py-3 text-start">Lottery Number</th>
                    <th class="px-3.5 py-3 text-start">Customer</th>
                    <th class="px-3.5 py-3 text-start">Amount (THB)</th>
                    <th class="px-3.5 py-3 text-start">Amount (MMK)</th>
                    <th class="px-3.5 py-3 text-start">Status</th>
                    <th class="px-3.5 py-3 text-start">Payment</th>
                    <th class="px-3.5 py-3 text-start">Date</th>
                    <th class="px-3.5 py-3 text-start">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $i => $transaction)
                    <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                        <td class="px-3.5 py-3">{{ $transactions->firstItem() + $i }}</td>
                        <td class="px-3.5 py-3 font-mono text-xs">{{ $transaction->transaction_number }}</td>
                        <td class="px-3.5 py-3">
                            <span class="font-mono font-semibold text-primary">
                                {{ $transaction->secondaryTicket?->ticket_number ?? '-' }}
                            </span>
                            @if($transaction->secondaryTicket?->withdraw_date)
                                <div class="text-xs text-default-400">
                                    🎯 {{ $transaction->secondaryTicket->withdraw_date->format('M d') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-3.5 py-3">
                            {{ $transaction->customer_display_name }}<br>
                            <span class="text-xs text-default-500">{{ $transaction->customer_display_phone }}</span>
                        </td>
                        <td class="px-3.5 py-3 font-semibold">฿{{ number_format($transaction->amount_thb, 2) }}</td>
                        <td class="px-3.5 py-3 font-semibold">
                            @if($transaction->amount_mmk)
                                {{ number_format($transaction->amount_mmk, 2) }} K
                            @else
                                <span class="text-default-400">-</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3">
                            @if($transaction->status == 'won')
                                <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">🎉 WON</span>
                                @if($transaction->prize_won)
                                    <div class="text-xs text-purple-600 mt-1">{{ $transaction->prize_won }}</div>
                                @endif
                            @elseif($transaction->status == 'not_won')
                                <span class="inline-flex px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Not Won</span>
                            @else
                                <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-xs">Pending</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3">
                            @if($transaction->is_paid)
                                <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">
                                    ✓ {{ $transaction->payment_method ?? 'Paid' }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 bg-danger/10 text-danger rounded text-xs">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3 text-xs">{{ $transaction->purchased_at->format('M d, Y H:i') }}</td>
                        <td class="px-3.5 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('secondary-transactions.show', $transaction) }}" class="btn btn-sm bg-info/10 text-info">
                                    <i class="size-4" data-lucide="eye"></i>
                                </a>
                                <a href="{{ route('secondary-transactions.edit', $transaction) }}" class="btn btn-sm bg-primary/10 text-primary">
                                    <i class="size-4" data-lucide="edit"></i>
                                </a>
                                @if(!$transaction->is_paid)
                                    <button type="button" class="btn btn-sm bg-success/10 text-success" onclick="showPayModal('{{ $transaction->id }}')">
                                        <i class="size-4" data-lucide="wallet"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3.5 py-8 text-center text-default-500">
                            No transactions found. <a href="{{ route('secondary-transactions.create') }}" class="text-primary hover:underline">Create your first transaction</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <p class="text-default-500 text-sm">Showing <b>{{ $transactions->count() }}</b> of <b>{{ $transactions->total() }}</b> Results</p>
            <nav class="flex items-center gap-2">
                {{ $transactions->links() }}
            </nav>
        </div>
    </div>

    {{-- Mark Paid Modal --}}
    <div id="payModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" onclick="hidePayModal()"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h5 class="text-lg font-semibold mb-4">Mark as Paid</h5>
                <form id="payForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="PromptPay">PromptPay</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn bg-success text-white">Confirm Payment</button>
                        <button type="button" class="btn bg-default-200" onclick="hidePayModal()">Cancel</button>
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
