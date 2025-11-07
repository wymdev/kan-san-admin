@extends('layouts.vertical', ['title' => 'Create Daily Quote'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Create Daily Quote'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">New Daily Quote</h4>
            </div>
            <div class="p-6">
                <form action="{{ route('daily-quotes.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="quote" class="block text-sm font-medium text-default-900 mb-2">Quote <span class="text-red-500">*</span></label>
                        <textarea 
                            id="quote" 
                            name="quote" 
                            rows="4"
                            class="form-input @error('quote') border-red-500 @enderror" 
                            placeholder="Enter inspirational quote"
                            required
                        >{{ old('quote') }}</textarea>
                        @error('quote')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-default-500 mt-1">Maximum 500 characters</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="author" class="block text-sm font-medium text-default-900 mb-2">Author (Optional)</label>
                            <input 
                                type="text" 
                                id="author" 
                                name="author" 
                                value="{{ old('author') }}"
                                class="form-input @error('author') border-red-500 @enderror" 
                                placeholder="Quote author"
                            />
                            @error('author')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-default-900 mb-2">Category <span class="text-red-500">*</span></label>
                            <select id="category" name="category" class="form-select @error('category') border-red-500 @enderror" required>
                                <option value="motivation" {{ old('category') == 'motivation' ? 'selected' : '' }}>Motivation</option>
                                <option value="inspiration" {{ old('category') == 'inspiration' ? 'selected' : '' }}>Inspiration</option>
                                <option value="success" {{ old('category') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="luck" {{ old('category') == 'luck' ? 'selected' : '' }}>Luck</option>
                            </select>
                            @error('category')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="scheduled_for" class="block text-sm font-medium text-default-900 mb-2">Schedule For (Optional)</label>
                        <input 
                            type="text" 
                            id="scheduled_for" 
                            name="scheduled_for" 
                            value="{{ old('scheduled_for') }}"
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
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-default-900">Active</span>
                        </label>
                        <p class="text-xs text-default-500 mt-1">Only active quotes will be sent</p>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="save"></i>
                            Create Quote
                        </button>
                        <a href="{{ route('daily-quotes.index') }}" class="btn bg-default-200 text-default-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/timepicker.js'])
@endsection
