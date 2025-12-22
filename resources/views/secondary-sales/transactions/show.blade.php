@extends('layouts.vertical', ['title' => 'Transaction Details'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Transaction Details'])

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h6 class="card-title">Transaction #{{ $secondaryTransaction->transaction_number }}</h6>
                    <a href="{{ route('secondary-transactions.edit', $secondaryTransaction) }}" class="btn btn-sm bg-primary/10 text-primary">
                        <i class="size-4 me-1" data-lucide="edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    {{-- Lottery Number Display --}}
                    <div class="text-center py-6 bg-gradient-to-r from-primary/5 to-success/5 rounded-xl mb-6">
                        <p class="text-sm text-default-500 mb-2">Lottery Number</p>
                        <div class="font-mono text-3xl font-bold text-primary tracking-widest">
                            {{ $secondaryTransaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                        </div>
                        @if($secondaryTransaction->secondaryTicket?->withdraw_date)
                            <p class="text-sm text-default-600 mt-2">
                                🎯 Draw Date: {{ $secondaryTransaction->secondaryTicket->withdraw_date->format('F d, Y') }}
                            </p>
                        @endif
                    </div>

                    {{-- Status Banner --}}
                    <div class="mb-6">
                        @if($secondaryTransaction->status == 'won')
                            <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl text-center">
                                <span class="text-4xl">🎉</span>
                                <h4 class="text-xl font-bold text-purple-700 mt-2">WINNER!</h4>
                                <p class="text-purple-600">{{ $secondaryTransaction->prize_won }}</p>
                            </div>
                        @elseif($secondaryTransaction->status == 'not_won')
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                                <span class="text-3xl">😔</span>
                                <h4 class="text-lg font-semibold text-gray-600 mt-2">Not Won</h4>
                                <p class="text-sm text-gray-500">Checked on {{ $secondaryTransaction->checked_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <div class="p-4 bg-warning/10 border border-warning/20 rounded-xl text-center">
                                <span class="text-3xl">⏳</span>
                                <h4 class="text-lg font-semibold text-warning mt-2">Pending Check</h4>
                                <p class="text-sm text-default-500">Results not yet checked</p>
                            </div>
                        @endif
                    </div>

                    {{-- Details Grid --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-default-500">Sale Type</p>
                            @if($secondaryTransaction->sale_type == 'own')
                                <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-sm">
                                    <i class="size-3 me-1" data-lucide="user-check"></i> Sell by My Own
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-sm">
                                    <i class="size-3 me-1" data-lucide="users"></i> Sold by Other
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Customer</p>
                            @if($secondaryTransaction->sale_type == 'own')
                                <p class="font-medium">{{ $secondaryTransaction->customer_display_name }}</p>
                                @if($secondaryTransaction->customer_display_phone)
                                    <p class="text-sm text-default-600">{{ $secondaryTransaction->customer_display_phone }}</p>
                                @endif
                            @else
                                <p class="text-default-400 italic">No customer info (sold by other)</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Amount</p>
                            <p class="font-semibold text-xl">฿{{ number_format($secondaryTransaction->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Purchase Date</p>
                            <p class="font-medium">{{ $secondaryTransaction->purchased_at->format('F d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Payment Status</p>
                            @if($secondaryTransaction->is_paid)
                                <span class="inline-flex px-3 py-1 bg-success/10 text-success rounded text-sm font-medium">
                                    ✓ Paid ({{ $secondaryTransaction->payment_method }})
                                </span>
                                @if($secondaryTransaction->payment_date)
                                    <p class="text-xs text-default-500 mt-1">{{ $secondaryTransaction->payment_date->format('M d, Y H:i') }}</p>
                                @endif
                            @else
                                <span class="inline-flex px-3 py-1 bg-danger/10 text-danger rounded text-sm font-medium">
                                    Unpaid
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Created By</p>
                            <p class="font-medium">{{ $secondaryTransaction->createdBy?->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Created At</p>
                            <p class="font-medium">{{ $secondaryTransaction->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    {{-- Public Link Section (only for 'own' sales) --}}
                    @if($secondaryTransaction->sale_type == 'own')
                        @php
                            // Prioritize batch_token (one link for all tickets from same customer+draw)
                            $displayToken = $secondaryTransaction->batch_token ?? $secondaryTransaction->public_token;
                            $isBatchLink = !empty($secondaryTransaction->batch_token);
                        @endphp
                        @if($displayToken)
                            <div class="mt-6 p-4 bg-info/5 border border-info/20 rounded-lg">
                                <p class="text-sm text-info font-semibold mb-2 flex items-center gap-2">
                                    <i class="size-4" data-lucide="link"></i>
                                    @if($isBatchLink)
                                        Customer Result Check Link (All Tickets)
                                    @else
                                        Single Ticket Result Link
                                    @endif
                                </p>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ route('public.customer-batch', ['token' => $displayToken]) }}" 
                                           class="form-input form-input-sm flex-1 font-mono text-xs" id="publicLink">
                                    <button onclick="copyLink()" class="btn btn-sm bg-info/10 text-info">
                                        <i class="size-4" data-lucide="copy"></i> Copy
                                    </button>
                                </div>
                                <p class="text-xs text-default-500 mt-2">
                                    @if($isBatchLink)
                                        📱 Share this link with the customer to check <strong>all their tickets</strong> for this draw date.
                                    @else
                                        Share this link with the customer to check this specific ticket.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif

                    @if($secondaryTransaction->notes)
                        <div class="mt-6 p-4 bg-default-100 rounded-lg">
                            <p class="text-sm text-default-500">Notes</p>
                            <p>{{ $secondaryTransaction->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Actions --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Actions</h6>
                </div>
                <div class="card-body space-y-3">
                    @if(!$secondaryTransaction->is_paid)
                        <form action="{{ route('secondary-transactions.mark-paid', $secondaryTransaction) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="Cash">
                            <button type="submit" class="btn bg-success text-white w-full" onclick="return confirm('Mark this transaction as paid?')">
                                <i class="size-4 me-1" data-lucide="wallet"></i> Mark as Paid
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('secondary-transactions.edit', $secondaryTransaction) }}" class="btn bg-primary/10 text-primary w-full">
                        <i class="size-4 me-1" data-lucide="edit"></i> Edit Transaction
                    </a>

                    @if($secondaryTransaction->secondaryTicket)
                        <a href="{{ route('secondary-tickets.show', $secondaryTransaction->secondaryTicket) }}" class="btn bg-info/10 text-info w-full">
                            <i class="size-4 me-1" data-lucide="ticket"></i> View Ticket
                        </a>
                    @endif

                    <form action="{{ route('secondary-transactions.destroy', $secondaryTransaction) }}" method="POST" onsubmit="return confirm('Delete this transaction?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-danger/10 text-danger w-full">
                            <i class="size-4 me-1" data-lucide="trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>

            @if($secondaryTransaction->drawResult)
                <div class="card mt-6">
                    <div class="card-header">
                        <h6 class="card-title">Draw Result</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-default-500">Draw Date</p>
                        <p class="font-medium">{{ $secondaryTransaction->drawResult->date_en }}</p>
                        <a href="{{ route('draw_results.show', $secondaryTransaction->drawResult->id) }}" class="text-primary text-sm hover:underline mt-2 inline-block">
                            View Full Results →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
<script>
function copyLink() {
    const input = document.getElementById('publicLink');
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    // Try modern API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(() => {
            showToast('Link copied to clipboard! 📋');
        }).catch(() => {
            fallbackCopyTextToClipboard(input.value);
        });
    } else {
        fallbackCopyTextToClipboard(input.value);
    }
}

function fallbackCopyTextToClipboard(text) {
    try {
        document.execCommand('copy');
        showToast('Link copied to clipboard! 📋');
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        alert('Failed to copy link. Please copy manually.');
    }
}

function showToast(message) {
    // Create simple toast
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 z-50 flex items-center gap-2';
    toast.innerHTML = `<i data-lucide="check-circle" class="size-4 text-green-400"></i> ${message}`;
    document.body.appendChild(toast);
    
    // Initialize icon
    lucide.createIcons();
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove after 3s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
