@extends('layouts.vertical', ['title' => 'Edit Announcement'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Edit Announcement'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Announcement</h4>
            </div>
            <div class="p-6">
                @if($announcement->is_sent)
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-center gap-2">
                            <i class="size-5" data-lucide="info"></i>
                            <span class="font-medium">This announcement has already been sent and cannot be modified.</span>
                        </div>
                    </div>
                @endif

                <form action="{{ route('announcements.update', $announcement) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-default-900 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title', $announcement->title) }}"
                            class="form-input @error('title') border-red-500 @enderror" 
                            placeholder="Enter announcement title"
                            {{ $announcement->is_sent ? 'readonly' : '' }}
                            required
                        />
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="body" class="block text-sm font-medium text-default-900 mb-2">
                            Message <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="body" 
                            name="body" 
                            rows="5"
                            class="form-input @error('body') border-red-500 @enderror" 
                            placeholder="Enter announcement message (max 1000 characters)"
                            {{ $announcement->is_sent ? 'readonly' : '' }}
                            required
                        >{{ old('body', $announcement->body) }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-default-500 mt-1">
                            <span id="charCount">{{ strlen($announcement->body) }}</span>/1000 characters
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-default-900 mb-2">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="type" 
                                name="type" 
                                class="form-select @error('type') border-red-500 @enderror" 
                                {{ $announcement->is_sent ? 'disabled' : '' }}
                                required
                            >
                                <option value="general" {{ old('type', $announcement->type) == 'general' ? 'selected' : '' }}>General</option>
                                <option value="promotion" {{ old('type', $announcement->type) == 'promotion' ? 'selected' : '' }}>Promotion</option>
                                <option value="maintenance" {{ old('type', $announcement->type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="update" {{ old('type', $announcement->type) == 'update' ? 'selected' : '' }}>Update</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-default-900 mb-2">
                                Schedule For (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="scheduled_at" 
                                name="scheduled_at" 
                                value="{{ old('scheduled_at', $announcement->scheduled_at ? $announcement->scheduled_at->format('Y-m-d H:i') : '') }}"
                                class="form-input @error('scheduled_at') border-red-500 @enderror"
                                data-provider="flatpickr" 
                                data-date-format="Y-m-d H:i"
                                data-enable-time="true"
                                placeholder="Select date and time"
                                {{ $announcement->is_sent ? 'disabled' : '' }}
                            />
                            @error('scheduled_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-default-500 mt-1">Leave empty to send immediately when published</p>
                        </div>
                    </div>

                    @if(!$announcement->is_sent)
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    name="is_published" 
                                    value="1" 
                                    {{ old('is_published', $announcement->is_published) ? 'checked' : '' }} 
                                    class="form-checkbox"
                                >
                                <span class="text-sm text-default-900">
                                    Publish and send notification
                                    @if($announcement->scheduled_at)
                                        (will send at scheduled time)
                                    @else
                                        immediately
                                    @endif
                                </span>
                            </label>
                            <p class="text-xs text-default-500 mt-1">
                                @if($announcement->is_published)
                                    Currently published. Uncheck to revert to draft.
                                @else
                                    Currently a draft. Check to publish and send.
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Status Information -->
                    <div class="border border-default-200 rounded-lg p-4 mb-4 bg-default-50">
                        <h5 class="text-sm font-semibold text-default-900 mb-3">Announcement Status</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-default-500">Status:</span>
                                <div class="mt-1">
                                    @if($announcement->is_sent)
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-green-100 text-green-700">
                                            <i class="size-3" data-lucide="check-circle"></i> Sent
                                        </span>
                                    @elseif($announcement->is_published)
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                            <i class="size-3" data-lucide="clock"></i> Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 py-1 px-2 rounded text-xs font-medium bg-default-200 text-default-700">
                                            <i class="size-3" data-lucide="file-text"></i> Draft
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($announcement->is_sent)
                                <div>
                                    <span class="text-default-500">Recipients:</span>
                                    <div class="mt-1 font-medium text-default-900">
                                        {{ $announcement->recipients_count }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-default-500">Success Rate:</span>
                                    <div class="mt-1">
                                        <span class="text-green-600 font-medium">{{ $announcement->success_count }}</span>
                                        @if($announcement->failed_count > 0)
                                            / <span class="text-red-600 font-medium">{{ $announcement->failed_count }} failed</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if($announcement->sent_at)
                            <div class="mt-3 pt-3 border-t border-default-200">
                                <span class="text-default-500 text-xs">Sent at:</span>
                                <span class="text-default-900 text-xs font-medium ml-2">
                                    {{ $announcement->sent_at->format('d M Y, H:i:s') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        @if(!$announcement->is_sent)
                            <button type="submit" class="btn bg-primary text-white">
                                <i class="size-4 me-1" data-lucide="save"></i>
                                Update Announcement
                            </button>
                        @endif
                        <a href="{{ route('announcements.index') }}" class="btn bg-default-200 text-default-600">
                            <i class="size-4 me-1" data-lucide="arrow-left"></i>
                            Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/timepicker.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('body');
            const charCount = document.getElementById('charCount');
            
            if (textarea && charCount) {
                textarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                    
                    if (this.value.length > 1000) {
                        charCount.classList.add('text-red-500');
                    } else {
                        charCount.classList.remove('text-red-500');
                    }
                });
            }
        });
    </script>
@endsection
