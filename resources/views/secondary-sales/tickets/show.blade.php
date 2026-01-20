@extends('layouts.vertical', ['title' => 'Ticket Details'])

@section('css')
<style>
    .detail-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .detail-card {
        background: rgb(31, 41, 55);
        border-color: rgb(55, 65, 81);
    }
    .detail-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dark .detail-header {
        border-color: rgb(55, 65, 81);
    }
    .detail-body {
        padding: 1.25rem;
    }
    .ticket-display {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
    }
    .dark .ticket-display {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }
    .ticket-number-large {
        font-family: 'Courier New', monospace;
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: 0.35em;
        color: rgb(var(--primary-rgb));
    }
    @media (max-width: 640px) {
        .ticket-number-large {
            font-size: 1.75rem;
            letter-spacing: 0.25em;
        }
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 640px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
    .info-item {
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 12px;
    }
    .dark .info-item {
        background: rgb(17, 24, 39);
    }
    .info-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }
    .dark .info-label {
        color: #9ca3af;
    }
    .info-value {
        font-weight: 600;
        color: #111827;
    }
    .dark .info-value {
        color: #f9fafb;
    }
    .action-btn-lg {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s;
        width: 100%;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }
    .table-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .table-container {
        border-color: rgb(55, 65, 81);
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Ticket Details'])

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Ticket Info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-indigo-500" data-lucide="ticket"></i> Ticket Information
                    </h6>
                    <div class="flex gap-2">
                        <a href="{{ route('secondary-tickets.edit', $secondaryTicket) }}" class="btn btn-sm bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center gap-1">
                            <i class="size-4" data-lucide="edit"></i>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                        <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" class="btn btn-sm bg-emerald-600 text-white rounded-lg flex items-center gap-1">
                            <i class="size-4" data-lucide="shopping-cart"></i>
                            <span class="hidden sm:inline">Sell</span>
                        </a>
                    </div>
                </div>
                <div class="detail-body">
                    <div class="ticket-display mb-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Ticket Number</p>
                        <div class="ticket-number-large">{{ $secondaryTicket->ticket_number }}</div>
                        @if($secondaryTicket->batch_number)
                            <span class="badge bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 mt-3">
                                Batch: {{ $secondaryTicket->batch_number }}
                            </span>
                        @endif
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <p class="info-label">Draw Date</p>
                            <p class="info-value">
                                @if($secondaryTicket->withdraw_date)
                                    {{ $secondaryTicket->withdraw_date->format('F d, Y') }}
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Price</p>
                            <p class="info-value text-emerald-600">
                                @if($secondaryTicket->price)
                                    ฿{{ number_format($secondaryTicket->price, 2) }}
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Ticket Type</p>
                            <p class="info-value capitalize">{{ $secondaryTicket->ticket_type ?? 'Normal' }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Source Seller</p>
                            <p class="info-value">{{ $secondaryTicket->source_seller ?? '-' }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Created By</p>
                            <p class="info-value">{{ $secondaryTicket->createdBy?->name ?? 'Unknown' }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Created At</p>
                            <p class="info-value">{{ $secondaryTicket->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($secondaryTicket->notes)
                        <div class="mt-5 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</p>
                            <p class="text-gray-900 dark:text-white">{{ $secondaryTicket->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transactions --}}
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-emerald-500" data-lucide="receipt"></i> Sales Transactions
                    </h6>
                    <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                        {{ $secondaryTicket->transactions->count() }}
                    </span>
                </div>
                @if($secondaryTicket->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    <th class="px-4 py-3 text-left">Transaction #</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-left">Amount</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Paid</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($secondaryTicket->transactions as $transaction)
                                    <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('secondary-transactions.show', $transaction) }}" class="font-mono text-primary hover:underline font-medium">
                                                {{ $transaction->transaction_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                            {{ $transaction->customer_display_name }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                            ฿{{ number_format($transaction->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($transaction->status == 'won')
                                                <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">🎉 WON</span>
                                            @elseif($transaction->status == 'not_won')
                                                <span class="badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Not Won</span>
                                            @else
                                                <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($transaction->is_paid)
                                                <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Paid</span>
                                            @else
                                                <span class="badge bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $transaction->purchased_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="detail-body text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="size-8 text-gray-400" data-lucide="shopping-cart"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 mb-3">No sales yet</p>
                        <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" class="text-primary hover:underline text-sm">
                            Create a sale →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            @if($secondaryTicket->source_image)
                <div class="detail-card">
                    <div class="detail-header">
                        <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="size-4 text-cyan-500" data-lucide="image"></i> Ticket Image
                        </h6>
                    </div>
                    <div class="detail-body">
                        <img src="{{ Storage::url($secondaryTicket->source_image) }}" alt="Ticket Image" 
                            class="w-full rounded-xl shadow-sm cursor-pointer hover:opacity-90 transition-opacity"
                            onclick="window.open(this.src, '_blank')">
                    </div>
                </div>
            @endif

            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-amber-500" data-lucide="zap"></i> Quick Actions
                    </h6>
                </div>
                <div class="detail-body space-y-3">
                    <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" 
                        class="action-btn-lg bg-emerald-600 hover:bg-emerald-700 text-white">
                        <i class="size-5" data-lucide="shopping-cart"></i> Create Sale
                    </a>
                    <a href="{{ route('secondary-tickets.edit', $secondaryTicket) }}" 
                        class="action-btn-lg bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
                        <i class="size-5" data-lucide="edit"></i> Edit Ticket
                    </a>
                    @if($secondaryTicket->transactions->count() === 0)
                        <form action="{{ route('secondary-tickets.destroy', $secondaryTicket) }}" method="POST" onsubmit="return confirm('Delete this ticket?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn-lg bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400">
                                <i class="size-5" data-lucide="trash"></i> Delete Ticket
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('secondary-tickets.index') }}" 
                        class="action-btn-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300">
                        <i class="size-5" data-lucide="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Ticket Status Summary --}}
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-violet-500" data-lucide="bar-chart-2"></i> Status
                    </h6>
                </div>
                <div class="detail-body">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Sales</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $secondaryTicket->transactions->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</span>
                            <span class="font-bold text-emerald-600">฿{{ number_format($secondaryTicket->transactions->sum('amount'), 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Winners</span>
                            <span class="font-bold text-violet-600">{{ $secondaryTicket->transactions->where('status', 'won')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
