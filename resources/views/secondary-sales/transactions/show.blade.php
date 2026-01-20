@extends('layouts.vertical', ['title' => 'Transaction Details'])

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
        padding: 1.5rem;
        text-align: center;
    }
    .dark .ticket-display {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }
    .ticket-number-large {
        font-family: 'Courier New', monospace;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 0.35em;
        color: rgb(var(--primary-rgb));
    }
    @media (max-width: 640px) {
        .ticket-number-large {
            font-size: 1.5rem;
            letter-spacing: 0.25em;
        }
    }
    .status-banner {
        padding: 1.25rem;
        border-radius: 16px;
        text-align: center;
    }
    .status-banner.won {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(167, 139, 250, 0.1) 100%);
        border: 1px solid rgba(139, 92, 246, 0.2);
    }
    .dark .status-banner.won {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(167, 139, 250, 0.2) 100%);
    }
    .status-banner.not-won {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }
    .dark .status-banner.not-won {
        background: rgb(55, 65, 81);
        border-color: rgb(75, 85, 99);
    }
    .status-banner.pending {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .dark .status-banner.pending {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(251, 191, 36, 0.2) 100%);
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
    .link-box {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, rgba(14, 165, 233, 0.05) 100%);
        border: 1px solid rgba(6, 182, 212, 0.2);
        border-radius: 12px;
        padding: 1rem;
    }
    .dark .link-box {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(14, 165, 233, 0.1) 100%);
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Transaction Details'])

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-indigo-500" data-lucide="receipt"></i>
                        <span class="font-mono">{{ $secondaryTransaction->transaction_number }}</span>
                    </h6>
                    <a href="{{ route('secondary-transactions.edit', $secondaryTransaction) }}" class="btn btn-sm bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center gap-1">
                        <i class="size-4" data-lucide="edit"></i>
                        <span class="hidden sm:inline">Edit</span>
                    </a>
                </div>
                <div class="detail-body">
                    {{-- Lottery Number Display --}}
                    <div class="ticket-display mb-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Lottery Number</p>
                        <div class="ticket-number-large">
                            {{ $secondaryTransaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                        </div>
                        @if($secondaryTransaction->secondaryTicket?->withdraw_date)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 flex items-center justify-center gap-1">
                                <i class="size-4" data-lucide="calendar"></i>
                                Draw: {{ $secondaryTransaction->secondaryTicket->withdraw_date->format('F d, Y') }}
                            </p>
                        @endif
                    </div>

                    {{-- Status Banner --}}
                    <div class="mb-6">
                        @if($secondaryTransaction->status == 'won')
                            <div class="status-banner won">
                                <span class="text-4xl">🎉</span>
                                <h4 class="text-xl font-bold text-violet-700 dark:text-violet-400 mt-2">WINNER!</h4>
                                <p class="text-violet-600 dark:text-violet-300">{{ $secondaryTransaction->prize_won }}</p>
                            </div>
                        @elseif($secondaryTransaction->status == 'not_won')
                            <div class="status-banner not-won">
                                <span class="text-3xl">😔</span>
                                <h4 class="text-lg font-semibold text-gray-600 dark:text-gray-300 mt-2">Not Won</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Checked on {{ $secondaryTransaction->checked_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <div class="status-banner pending">
                                <span class="text-3xl">⏳</span>
                                <h4 class="text-lg font-semibold text-amber-600 dark:text-amber-400 mt-2">Pending Check</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Results not yet checked</p>
                            </div>
                        @endif
                    </div>

                    {{-- Details Grid --}}
                    <div class="info-grid">
                        <div class="info-item">
                            <p class="info-label">Sale Type</p>
                            @if($secondaryTransaction->sale_type == 'own')
                                <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                    <i class="size-3 mr-1" data-lucide="user-check"></i> Sell by My Own
                                </span>
                            @else
                                <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                    <i class="size-3 mr-1" data-lucide="users"></i> Sold by Other
                                </span>
                            @endif
                        </div>
                        <div class="info-item">
                            <p class="info-label">Customer</p>
                            @if($secondaryTransaction->sale_type == 'own')
                                <p class="info-value">{{ $secondaryTransaction->customer_display_name }}</p>
                                @if($secondaryTransaction->customer_display_phone)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $secondaryTransaction->customer_display_phone }}</p>
                                @endif
                            @else
                                <p class="text-gray-400 italic text-sm">No customer info</p>
                            @endif
                        </div>
                        <div class="info-item">
                            <p class="info-label">Amount</p>
                            <p class="info-value text-xl text-emerald-600">฿{{ number_format($secondaryTransaction->amount, 2) }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Purchase Date</p>
                            <p class="info-value">{{ $secondaryTransaction->purchased_at->format('F d, Y H:i') }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Payment Status</p>
                            @if($secondaryTransaction->is_paid)
                                <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                    ✓ Paid ({{ $secondaryTransaction->payment_method }})
                                </span>
                                @if($secondaryTransaction->payment_date)
                                    <p class="text-xs text-gray-500 mt-1">{{ $secondaryTransaction->payment_date->format('M d, Y H:i') }}</p>
                                @endif
                            @else
                                <span class="badge bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">
                                    Unpaid
                                </span>
                            @endif
                        </div>
                        <div class="info-item">
                            <p class="info-label">Created By</p>
                            <p class="info-value">{{ $secondaryTransaction->createdBy?->name ?? 'Unknown' }}</p>
                        </div>
                    </div>

                    {{-- Public Link Section --}}
                    @if($secondaryTransaction->sale_type == 'own')
                        @php
                            $displayToken = $secondaryTransaction->batch_token ?? $secondaryTransaction->public_token;
                            $isBatchLink = !empty($secondaryTransaction->batch_token);
                        @endphp
                        @if($displayToken)
                            <div class="link-box mt-6">
                                <p class="text-sm text-cyan-700 dark:text-cyan-400 font-semibold mb-2 flex items-center gap-2">
                                    <i class="size-4" data-lucide="link"></i>
                                    @if($isBatchLink)
                                        Customer Result Check Link (All Tickets)
                                    @else
                                        Single Ticket Result Link
                                    @endif
                                </p>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ route('public.customer-batch', ['token' => $displayToken]) }}" 
                                           class="form-input form-input-sm flex-1 font-mono text-xs rounded-lg" id="publicLink">
                                    <button onclick="copyLink()" class="btn btn-sm bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 rounded-lg flex items-center gap-1">
                                        <i class="size-4" data-lucide="copy"></i>
                                        <span class="hidden sm:inline">Copy</span>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    @if($isBatchLink)
                                        📱 Share this link with the customer to check <strong>all their tickets</strong> for this draw.
                                    @else
                                        Share this link with the customer to check this specific ticket.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif

                    @if($secondaryTransaction->notes)
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</p>
                            <p class="text-gray-900 dark:text-white">{{ $secondaryTransaction->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-amber-500" data-lucide="zap"></i> Actions
                    </h6>
                </div>
                <div class="detail-body space-y-3">
                    @if(!$secondaryTransaction->is_paid)
                        <form action="{{ route('secondary-transactions.mark-paid', $secondaryTransaction) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="Cash">
                            <button type="submit" class="action-btn-lg bg-emerald-600 hover:bg-emerald-700 text-white" onclick="return confirm('Mark this transaction as paid?')">
                                <i class="size-5" data-lucide="wallet"></i> Mark as Paid
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('secondary-transactions.edit', $secondaryTransaction) }}" class="action-btn-lg bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
                        <i class="size-5" data-lucide="edit"></i> Edit Transaction
                    </a>

                    @if($secondaryTransaction->secondaryTicket)
                        <a href="{{ route('secondary-tickets.show', $secondaryTransaction->secondaryTicket) }}" class="action-btn-lg bg-cyan-50 dark:bg-cyan-900/20 hover:bg-cyan-100 dark:hover:bg-cyan-900/40 text-cyan-600 dark:text-cyan-400">
                            <i class="size-5" data-lucide="ticket"></i> View Ticket
                        </a>
                    @endif

                    <form action="{{ route('secondary-transactions.destroy', $secondaryTransaction) }}" method="POST" onsubmit="return confirm('Delete this transaction?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn-lg bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400">
                            <i class="size-5" data-lucide="trash"></i> Delete
                        </button>
                    </form>

                    <a href="{{ route('secondary-transactions.index') }}" class="action-btn-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300">
                        <i class="size-5" data-lucide="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            @if($secondaryTransaction->drawResult)
                <div class="detail-card">
                    <div class="detail-header">
                        <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="size-4 text-violet-500" data-lucide="trophy"></i> Draw Result
                        </h6>
                    </div>
                    <div class="detail-body">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Draw Date</p>
                        <p class="font-semibold text-gray-900 dark:text-white mb-3">{{ $secondaryTransaction->drawResult->date_en }}</p>
                        <a href="{{ route('draw_results.show', $secondaryTransaction->drawResult->id) }}" class="text-primary text-sm hover:underline flex items-center gap-1">
                            View Full Results <i class="size-4" data-lucide="arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Quick Stats --}}
            <div class="detail-card">
                <div class="detail-header">
                    <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="size-4 text-emerald-500" data-lucide="bar-chart-2"></i> Summary
                    </h6>
                </div>
                <div class="detail-body space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Transaction #</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $secondaryTransaction->transaction_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Amount</span>
                        <span class="font-bold text-emerald-600">฿{{ number_format($secondaryTransaction->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        @if($secondaryTransaction->status == 'won')
                            <span class="badge bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">🎉 Won</span>
                        @elseif($secondaryTransaction->status == 'not_won')
                            <span class="text-gray-500">Not Won</span>
                        @else
                            <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Payment</span>
                        @if($secondaryTransaction->is_paid)
                            <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Paid</span>
                        @else
                            <span class="badge bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
async function copyLink() {
    const input = document.getElementById('publicLink');
    const text = input.value;
    
    try {
        await navigator.clipboard.writeText(text);
        showToast('Link copied to clipboard! 📋');
    } catch (err) {
        input.select();
        input.setSelectionRange(0, 99999);
        showToast('Link selected! Press Ctrl+C to copy 📋');
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2';
    toast.innerHTML = `<i data-lucide="check-circle" class="size-4 text-green-400"></i> ${message}`;
    document.body.appendChild(toast);
    
    lucide.createIcons();
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
