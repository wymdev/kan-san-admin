@extends('layouts.vertical', ['title' => 'Create Announcement'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Create Announcement'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">New Announcement</h4>
            </div>
            <div class="p-6">
                <form action="{{ route('announcements.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-default-900 mb-2">Title <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title') }}"
                            class="form-input @error('title') border-red-500 @enderror" 
                            placeholder="Enter announcement title"
                            required
                        />
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="body" class="block text-sm font-medium text-default-900 mb-2">Message <span class="text-red-500">*</span></label>
                        <textarea 
                            id="body" 
                            name="body" 
                            rows="4"
                            class="form-input @error('body') border-red-500 @enderror" 
                            placeholder="Enter announcement message (max 1000 characters)"
                            required
                        >{{ old('body') }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-default-500 mt-1">Maximum 1000 characters</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-default-900 mb-2">Type <span class="text-red-500">*</span></label>
                            <select id="type" name="type" class="form-select @error('type') border-red-500 @enderror" required>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="promotion" {{ old('type') == 'promotion' ? 'selected' : '' }}>Promotion</option>
                                <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="update" {{ old('type') == 'update' ? 'selected' : '' }}>Update</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-default-900 mb-2">Schedule For (Optional)</label>
                            <input 
                                type="text" 
                                id="scheduled_at" 
                                name="scheduled_at" 
                                value="{{ old('scheduled_at') }}"
                                class="form-input @error('scheduled_at') border-red-500 @enderror"
                                data-provider="flatpickr" 
                                data-date-format="Y-m-d H:i"
                                data-enable-time="true"
                                placeholder="Select date and time"
                            />
                            @error('scheduled_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-default-500 mt-1">Leave empty to send immediately when published</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-default-900">Publish and send notification immediately</span>
                        </label>
                        <p class="text-xs text-default-500 mt-1">If unchecked, announcement will be saved as draft</p>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="send"></i>
                            Create Announcement
                        </button>
                        <a href="{{ route('announcements.index') }}" class="btn bg-default-200 text-default-600">
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
