@extends('layouts.vertical', ['title' => 'Edit Secondary Ticket'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .form-card {
        background: rgb(31, 41, 55);
        border-color: rgb(55, 65, 81);
    }
    .form-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .dark .form-header {
        border-color: rgb(55, 65, 81);
    }
    .form-body {
        padding: 1.5rem;
    }
    .form-section {
        margin-bottom: 1.5rem;
    }
    .form-section:last-child {
        margin-bottom: 0;
    }
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }
    .dark .form-label {
        color: #e5e7eb;
    }
    .ticket-preview {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .dark .ticket-preview {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }
    .ticket-number-display {
        font-family: 'Courier New', monospace;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0.3em;
        color: rgb(var(--primary-rgb));
    }
    .batch-highlight {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(99, 102, 241, 0.1) 100%);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 12px;
        padding: 1rem;
    }
    .dark .batch-highlight {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0.2) 100%);
        border-color: rgba(99, 102, 241, 0.3);
    }
    .image-preview {
        max-height: 160px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .btn-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Edit Ticket'])

    <div class="max-w-2xl mx-auto">
        <div class="form-card">
            <div class="form-header">
                <h6 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="size-4 text-indigo-500" data-lucide="edit-3"></i> Edit Ticket
                </h6>
                <a href="{{ route('secondary-tickets.show', $secondaryTicket) }}" class="btn btn-sm bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 rounded-lg flex items-center gap-1">
                    <i class="size-4" data-lucide="eye"></i>
                    <span>View</span>
                </a>
            </div>
            <div class="form-body">
                {{-- Ticket Preview --}}
                <div class="ticket-preview">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Current Ticket</p>
                    <div class="ticket-number-display">{{ $secondaryTicket->ticket_number }}</div>
                </div>

                <form action="{{ route('secondary-tickets.update', $secondaryTicket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Batch Number --}}
                    <div class="form-section">
                        <div class="batch-highlight">
                            <label class="form-label flex items-center gap-2 mb-2">
                                <i class="size-4 text-indigo-500" data-lucide="package"></i> Batch Number 
                                <span class="text-xs text-gray-400 font-normal">(groups tickets together)</span>
                            </label>
                            <input type="text" name="batch_number" value="{{ old('batch_number', $secondaryTicket->batch_number) }}" 
                                   class="form-input rounded-lg @error('batch_number') border-danger @enderror" 
                                   placeholder="e.g., 45, 46, BATCH-A">
                            <p class="text-xs text-gray-500 mt-2">Tickets with the same batch will be grouped for customer's public link</p>
                            @error('batch_number')
                                <span class="text-danger text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Ticket Number --}}
                    <div class="form-section">
                        <label class="form-label">Ticket Number <span class="text-danger">*</span></label>
                        <input type="text" name="numbers" value="{{ old('numbers', $secondaryTicket->ticket_number) }}" 
                               class="form-input rounded-lg font-mono text-lg tracking-widest @error('numbers') border-danger @enderror" required>
                        @error('numbers')
                            <span class="text-danger text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid md:grid-cols-3 gap-4 form-section">
                        <div>
                            <label class="form-label">Draw Date</label>
                            <input type="date" name="withdraw_date" 
                                   value="{{ old('withdraw_date', $secondaryTicket->withdraw_date?->format('Y-m-d')) }}" 
                                   class="form-input rounded-lg">
                        </div>

                        <div>
                            <label class="form-label">Price (฿)</label>
                            <input type="number" name="price" 
                                   value="{{ old('price', $secondaryTicket->price) }}" 
                                   step="0.01" min="0" class="form-input rounded-lg">
                        </div>

                        <div>
                            <label class="form-label">Ticket Type</label>
                            <select name="ticket_type" id="ticketType" class="form-select rounded-lg">
                                <option value="normal" {{ $secondaryTicket->ticket_type == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="special" {{ $secondaryTicket->ticket_type == 'special' ? 'selected' : '' }}>Special</option>
                                <option value="lucky" {{ $secondaryTicket->ticket_type == 'lucky' ? 'selected' : '' }}>Lucky</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 form-section">
                        <div>
                            <label class="form-label">Source Seller</label>
                            <input type="text" name="source_seller" 
                                   value="{{ old('source_seller', $secondaryTicket->source_seller) }}" 
                                   class="form-input rounded-lg" placeholder="Where did you buy this ticket?">
                        </div>

                        <div>
                            <label class="form-label">Period</label>
                            <input type="number" name="period" value="{{ old('period', $secondaryTicket->period) }}" 
                                   class="form-input rounded-lg" placeholder="Lottery period number">
                        </div>
                    </div>

                    <div class="form-section">
                        <label class="form-label">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="2" class="form-input rounded-lg" placeholder="Optional notes...">{{ old('notes', $secondaryTicket->notes) }}</textarea>
                    </div>

                    @if($secondaryTicket->source_image)
                        <div class="form-section p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <label class="form-label">Current Image</label>
                            <div class="mt-2">
                                <img src="{{ Storage::url($secondaryTicket->source_image) }}" alt="Ticket Image" class="image-preview">
                            </div>
                        </div>
                    @endif

                    <div class="form-section">
                        <label class="form-label">{{ $secondaryTicket->source_image ? 'Replace Image' : 'Attach Image' }} <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="file" name="source_image" accept="image/*" class="form-input rounded-lg">
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="btn-action bg-primary hover:bg-primary/90 text-white flex-1">
                            <i class="size-5" data-lucide="save"></i> Update Ticket
                        </button>
                        <a href="{{ route('secondary-tickets.index') }}" class="btn-action bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 flex-1">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketTypeSelect = document.getElementById('ticketType');
    if (ticketTypeSelect) {
        new Choices(ticketTypeSelect, {
            searchEnabled: false,
            itemSelectText: '',
            shouldSort: false,
        });
    }
});
</script>
@endsection
