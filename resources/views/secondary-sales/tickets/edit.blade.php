@extends('layouts.vertical', ['title' => 'Edit Secondary Ticket'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Edit Ticket'])

    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h6 class="card-title flex items-center gap-2">
                <i class="size-4" data-lucide="edit-3"></i> Edit Ticket #{{ $secondaryTicket->ticket_number }}
            </h6>
            <a href="{{ route('secondary-tickets.show', $secondaryTicket) }}" class="btn btn-sm bg-info/10 text-info">
                <i class="size-4 me-1" data-lucide="eye"></i> View Details
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('secondary-tickets.update', $secondaryTicket) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Batch Number --}}
                <div class="p-4 bg-primary/5 border border-primary/20 rounded-lg">
                    <label class="form-label font-semibold flex items-center gap-2">
                        <i class="size-4" data-lucide="package"></i> Batch Number <span class="text-xs text-default-400 font-normal">(group tickets)</span>
                    </label>
                    <input type="text" name="batch_number" value="{{ old('batch_number', $secondaryTicket->batch_number) }}" 
                           class="form-input @error('batch_number') border-danger @enderror" 
                           placeholder="e.g., 45, 46, BATCH-A">
                    <p class="text-xs text-default-400 mt-1">Tickets with same batch will be grouped for customer's public link</p>
                    @error('batch_number')
                        <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Ticket Number --}}
                <div class="form-group">
                    <label class="form-label">Ticket Number <span class="text-danger">*</span></label>
                    <input type="text" name="numbers" value="{{ old('numbers', $secondaryTicket->ticket_number) }}" 
                           class="form-input font-mono text-lg tracking-widest @error('numbers') border-danger @enderror" required>
                    @error('numbers')
                        <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="form-label">Draw Date</label>
                        <input type="date" name="withdraw_date" 
                               value="{{ old('withdraw_date', $secondaryTicket->withdraw_date?->format('Y-m-d')) }}" 
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price (฿)</label>
                        <input type="number" name="price" 
                               value="{{ old('price', $secondaryTicket->price) }}" 
                               step="0.01" min="0" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ticket Type</label>
                        <select name="ticket_type" id="ticketType" class="form-select searchable-select">
                            <option value="normal" {{ $secondaryTicket->ticket_type == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="special" {{ $secondaryTicket->ticket_type == 'special' ? 'selected' : '' }}>Special</option>
                            <option value="lucky" {{ $secondaryTicket->ticket_type == 'lucky' ? 'selected' : '' }}>Lucky</option>
                        </select>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Source Seller</label>
                        <input type="text" name="source_seller" 
                               value="{{ old('source_seller', $secondaryTicket->source_seller) }}" 
                               class="form-input" placeholder="Where did you buy this ticket?">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Period</label>
                        <input type="number" name="period" value="{{ old('period', $secondaryTicket->period) }}" class="form-input" placeholder="Lottery period number">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-input" placeholder="Optional notes...">{{ old('notes', $secondaryTicket->notes) }}</textarea>
                </div>

                @if($secondaryTicket->source_image)
                    <div class="p-4 bg-default-50 rounded-lg">
                        <label class="form-label">Current Image</label>
                        <div class="mt-2">
                            <img src="{{ Storage::url($secondaryTicket->source_image) }}" alt="Ticket Image" class="max-h-40 rounded-lg shadow-md">
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ $secondaryTicket->source_image ? 'Replace Image' : 'Attach Image' }} <span class="text-xs text-default-400">(optional)</span></label>
                    <input type="file" name="source_image" accept="image/*" class="form-input">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="save"></i> Update Ticket
                    </button>
                    <a href="{{ route('secondary-tickets.index') }}" class="btn bg-default-200 text-default-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Choices.js for ticket type
    const ticketTypeSelect = document.getElementById('ticketType');
    if (ticketTypeSelect) {
        new Choices(ticketTypeSelect, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Select ticket type...',
            searchPlaceholderValue: 'Type to search...',
        });
    }
});
</script>
@endsection
