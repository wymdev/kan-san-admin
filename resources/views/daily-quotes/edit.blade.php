@extends('layouts.vertical', ['title' => 'Edit Daily Quote'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Edit Daily Quote'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Daily Quote</h4>
            </div>
            <div class="p-6">
                @if($quote->is_sent)
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-center gap-2">
                            <i class="size-5" data-lucide="info"></i>
                            <span class="font-medium">This quote has already been sent to customers.</span>
                        </div>
                    </div>
                @endif

                <form action="{{ route('daily-quotes.update', $quote) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="quote" class="block text-sm font-medium text-default-900 mb-2">
                            Quote <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="quote" 
                            name="quote" 
                            rows="5"
                            class="form-input @error('quote') border-red-500 @enderror" 
                            placeholder="Enter inspirational quote"
                            {{ $quote->is_sent ? 'readonly' : '' }}
                            required
                        >{{ old('quote', $quote->quote) }}</textarea>
                        @error('quote')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-default-500 mt-1">
                            <span id="charCount">{{ strlen($quote->quote) }}</span>/500 characters
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="author" class="block text-sm font-medium text-default-900 mb-2">
                                Author (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="author" 
                                name="author" 
                                value="{{ old('author', $quote->author) }}"
                                class="form-input @error('author') border-red-500 @enderror" 
                                placeholder="Quote author"
                                {{ $quote->is_sent ? 'readonly' : '' }}
                            />
                            @error('author')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-default-900 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="category" 
                                name="category" 
                                class="form-select @error('category') border-red-500 @enderror" 
                                {{ $quote->is_sent ? 'disabled' : '' }}
                                required
                            >
                                <option value="motivation" {{ old('category', $quote->category) == 'motivation' ? 'selected' : '' }}>Motivation</option>
                                <option value="inspiration" {{ old('category', $quote->category) == 'inspiration' ? 'selected' : '' }}>Inspiration</option>
                                <option value="success" {{ old('category', $quote->category) == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="luck" {{ old('category', $quote->category) == 'luck' ? 'selected' : '' }}>Luck</option>
                            </select>
                            @error('category')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if(!$quote->is_sent)
                        <div class="mb-4">
                            <label for="scheduled_for" class="block text-sm font-medium text-default-900 mb-2">
                                Schedule For (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="scheduled_for" 
                                name="scheduled_for" 
                                value="{{ old('scheduled_for', $quote->scheduled_for ? $quote->scheduled_for->format('Y-m-d') : '') }}"
                                class="form-input @error('scheduled_for') border-red-500 @enderror"
                                data-provider="flatpickr" 
                                data-date-format="Y-m-d"
                                placeholder="Select date"
                            />
                            @error('scheduled_for')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-default-500 mt-1">Leave empty for automatic daily scheduling</p>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    name="is_active" 
                                    value="1" 
                                    {{ old('is_active', $quote->is_active) ? 'checked' : '' }} 
                                    class="form-checkbox"
                                >
                                <span class="text-sm text-default-900">Active</span>
                            </label>
                            <p class="text-xs text-default-500 mt-1">
                                @if($quote->is_active)
                                    This quote is active and will be sent according to schedule
                                @else
                                    This quote is inactive and will not be sent
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Status Information -->
                    <div class="border border-default-200 rounded-lg p-4 mb-4 bg-default-50">
                        <h5 class="text-sm font-semibold text-default-900 mb-3">Quote Status</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-default-500">Status:</span>
                                <div class="mt-1">
                                    @if($quote->is_sent)
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-green-100 text-green-700">
                                            <i class="size-3" data-lucide="check-circle"></i> Sent
                                        </span>
                                    @elseif($quote->is_active)
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                            <i class="size-3" data-lucide="clock"></i> Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-default-200 text-default-700">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($quote->is_sent)
                                <div>
                                    <span class="text-default-500">Recipients:</span>
                                    <div class="mt-1 font-medium text-default-900">
                                        {{ $quote->recipients_count }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-default-500">Sent at:</span>
                                    <div class="mt-1 font-medium text-default-900">
                                        {{ $quote->sent_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            @elseif($quote->scheduled_for)
                                <div>
                                    <span class="text-default-500">Scheduled for:</span>
                                    <div class="mt-1 font-medium text-default-900">
                                        {{ $quote->scheduled_for->format('d M Y') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 pt-3 border-t border-default-200">
                            <span class="text-default-500 text-xs">Created at:</span>
                            <span class="text-default-900 text-xs font-medium ml-2">
                                {{ $quote->created_at->format('d M Y, H:i:s') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        @if(!$quote->is_sent)
                            <button type="submit" class="btn bg-primary text-white">
                                <i class="size-4 me-1" data-lucide="save"></i>
                                Update Quote
                            </button>
                            <form action="{{ route('daily-quotes.send-now', $quote) }}" method="POST" class="inline">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="btn bg-green-600 text-white hover:bg-green-700"
                                    onclick="return confirm('Send this quote to all customers now?')"
                                >
                                    <i class="size-4 me-1" data-lucide="send"></i>
                                    Send Now
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('daily-quotes.index') }}" class="btn bg-default-200 text-default-600">
                            <i class="size-4 me-1" data-lucide="arrow-left"></i>
                            Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Mobile Notification Preview</h4>
            </div>
            <div class="p-6">
                <div class="max-w-md mx-auto">
                    <div class="bg-white border border-default-200 rounded-lg shadow-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                                    <i class="size-6 text-white" data-lucide="quote"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-default-900">Daily Quote</p>
                                <p class="text-sm text-default-600 mt-1" id="previewQuote">
                                    {{ strlen($quote->quote) > 100 ? substr($quote->quote, 0, 97) . '...' : $quote->quote }}
                                </p>
                                @if($quote->author)
                                    <p class="text-xs text-default-500 mt-1">- {{ $quote->author }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/timepicker.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('quote');
            const charCount = document.getElementById('charCount');
            const preview = document.getElementById('previewQuote');
            
            if (textarea && charCount) {
                textarea.addEventListener('input', function() {
                    const length = this.value.length;
                    charCount.textContent = length;
                    
                    // Update character count color
                    if (length > 500) {
                        charCount.classList.add('text-red-500');
                    } else {
                        charCount.classList.remove('text-red-500');
                    }
                    
                    // Update preview
                    if (preview) {
                        const text = this.value.length > 100 
                            ? this.value.substring(0, 97) + '...' 
                            : this.value;
                        preview.textContent = text || 'Your quote will appear here...';
                    }
                });
            }
        });
    </script>
@endsection
