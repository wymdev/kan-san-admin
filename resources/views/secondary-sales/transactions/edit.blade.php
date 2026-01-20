@extends('layouts.vertical', ['title' => 'Edit Transaction'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    /* Enhanced Choices.js Styling */
    .choices__inner {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 0.875rem;
        min-height: 2.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }
    .choices__inner:focus,
    .choices[data-type*="select-one"].is-open .choices__inner {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .choices__list--dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        margin-top: 0.25rem;
        z-index: 50;
    }
    .choices__list--dropdown .choices__item--selectable {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
    }
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }
    .choices__input {
        font-size: 0.875rem;
        padding: 0.25rem 0;
    }
    .choices__placeholder {
        opacity: 0.5;
    }
    
    /* Enhanced Select Box Styling */
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
        padding-right: 2.5rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #ffffff;
        font-size: 0.875rem;
        color: #374151;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .dark .form-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
        background-color: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
    }

    .form-select:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .dark .form-select:hover {
        border-color: #60a5fa;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    }

    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }
    
    .dark .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        background-color: #374151;
    }
    
    .form-select:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f9fafb;
    }
    
    .dark .form-select:disabled {
        background-color: #1f2937;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Edit Transaction'])

    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h6 class="card-title flex items-center gap-2">
                <i class="size-4" data-lucide="edit-3"></i> Edit Transaction #{{ $secondaryTransaction->transaction_number }}
            </h6>
            <a href="{{ route('secondary-transactions.show', $secondaryTransaction) }}" class="btn btn-sm bg-info/10 text-info">
                <i class="size-4 me-1" data-lucide="eye"></i> View Details
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('secondary-transactions.update', $secondaryTransaction) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Ticket Info (read-only display) --}}
                <div class="p-4 bg-primary/5 rounded-lg border border-primary/20">
                    <h6 class="text-sm font-semibold mb-2 flex items-center gap-2"><i class="size-4" data-lucide="ticket"></i> Ticket</h6>
                    <span class="font-mono text-2xl font-bold text-primary tracking-widest">
                        {{ $secondaryTransaction->secondaryTicket?->ticket_number ?? 'N/A' }}
                    </span>
                    @if($secondaryTransaction->secondaryTicket?->withdraw_date)
                        <p class="text-sm text-default-500 mt-1">Draw: {{ $secondaryTransaction->secondaryTicket->withdraw_date->format('M d, Y') }}</p>
                    @endif
                    <input type="hidden" name="secondary_ticket_id" value="{{ $secondaryTransaction->secondary_ticket_id }}">
                </div>

                {{-- Customer Section --}}
                <div>
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4" data-lucide="user"></i> Customer Information</h6>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" value="{{ $secondaryTransaction->customer_name ?? $secondaryTransaction->customer?->full_name }}" 
                                   class="form-input bg-default-100 text-default-500" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="customer_phone" value="{{ $secondaryTransaction->customer_phone ?? $secondaryTransaction->customer?->phone_number }}" 
                                   class="form-input bg-default-100 text-default-500" readonly>
                        </div>
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div>
                    <h6 class="text-base font-semibold mb-3 flex items-center gap-2"><i class="size-4" data-lucide="banknote"></i> Payment Details</h6>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Amount (THB)</label>
                            <input type="number" name="amount_thb" 
                                   value="{{ old('amount_thb', $secondaryTransaction->amount_thb) }}" 
                                   step="0.01" min="0" class="form-input @error('amount_thb') border-danger @enderror" 
                                   placeholder="0.00">
                            @error('amount_thb')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount (MMK) <span class="text-xs text-default-400">optional</span></label>
                            <input type="number" name="amount_mmk" 
                                   value="{{ old('amount_mmk', $secondaryTransaction->amount_mmk) }}" 
                                   step="0.01" min="0" class="form-input @error('amount_mmk') border-danger @enderror" 
                                   placeholder="0.00">
                            @error('amount_mmk')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div class="form-group">
                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="purchased_at" 
                                   value="{{ old('purchased_at', $secondaryTransaction->purchased_at->format('Y-m-d\TH:i')) }}" 
                                   class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="paymentMethod" class="form-select searchable-select">
                                <option value="">Not paid</option>
                                <option value="Cash" {{ $secondaryTransaction->payment_method == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank Transfer" {{ $secondaryTransaction->payment_method == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="PromptPay" {{ $secondaryTransaction->payment_method == 'PromptPay' ? 'selected' : '' }}>PromptPay</option>
                                <option value="KBZPay" {{ $secondaryTransaction->payment_method == 'KBZPay' ? 'selected' : '' }}>KBZPay</option>
                                <option value="WavePay" {{ $secondaryTransaction->payment_method == 'WavePay' ? 'selected' : '' }}>WavePay</option>
                                <option value="Other" {{ $secondaryTransaction->payment_method == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_paid" id="isPaid" value="1" class="form-checkbox" 
                                   {{ $secondaryTransaction->is_paid ? 'checked' : '' }}>
                            <span class="text-sm text-default-600">Mark as paid</span>
                        </label>
                    </div>
                </div>

                {{-- Status Display --}}
                @if($secondaryTransaction->checked_at)
                    <div class="p-4 bg-default-100 rounded-lg">
                        <h6 class="text-sm font-semibold mb-2 flex items-center gap-2"><i class="size-4" data-lucide="clipboard-check"></i> Result Status</h6>
                        <div class="flex items-center gap-3">
                            @if($secondaryTransaction->status == 'won')
                                <span class="inline-flex px-3 py-1 bg-purple-100 text-purple-700 rounded font-bold"><i class="size-4 me-1" data-lucide="trophy"></i> WON - {{ $secondaryTransaction->prize_won }}</span>
                            @elseif($secondaryTransaction->status == 'not_won')
                                <span class="inline-flex px-3 py-1 bg-gray-100 text-gray-600 rounded"><i class="size-4 me-1" data-lucide="x-circle"></i> Not Won</span>
                            @else
                                <span class="inline-flex px-3 py-1 bg-warning/10 text-warning rounded"><i class="size-4 me-1" data-lucide="clock"></i> Pending</span>
                            @endif
                            <span class="text-sm text-default-500">Checked: {{ $secondaryTransaction->checked_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                <div>
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-input" placeholder="Optional notes...">{{ old('notes', $secondaryTransaction->notes) }}</textarea>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <div class="flex gap-3">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="save"></i> Update Transaction
                        </button>
                        <a href="{{ route('secondary-transactions.index') }}" class="btn bg-default-200 text-default-700">Cancel</a>
                    </div>
                    
                    <button type="button" class="btn bg-danger/10 text-danger hover:bg-danger hover:text-white transition-colors" onclick="if(confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) document.getElementById('delete-transaction-form').submit();">
                        <i class="size-4 me-1" data-lucide="trash-2"></i> Delete
                    </button>
                </div>
            </form>

            <form id="delete-transaction-form" action="{{ route('secondary-transactions.destroy', $secondaryTransaction) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Choices.js for payment method
    const paymentSelect = document.getElementById('paymentMethod');
    if (paymentSelect) {
        new Choices(paymentSelect, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Select payment method...',
            searchPlaceholderValue: 'Type to search...',
        });
    }

    // Payment method auto-check paid
    paymentSelect.addEventListener('change', function() {
        document.getElementById('isPaid').checked = this.value !== '';
});
</script>
@endsection
