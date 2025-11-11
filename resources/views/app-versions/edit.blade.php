@extends('layouts.vertical', ['title' => 'Edit App Version'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Edit Version'])

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Version Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('app-versions.update', $version) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="version" class="form-label">Version <span class="text-red-500">*</span></label>
                            <input type="text" name="version" id="version" class="form-input @error('version') border-red-500 @enderror" value="{{ old('version', $version->version) }}" placeholder="e.g., 1.0.0" required>
                            @error('version')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="version_code" class="form-label">Version Code <span class="text-red-500">*</span></label>
                            <input type="number" name="version_code" id="version_code" class="form-input @error('version_code') border-red-500 @enderror" value="{{ old('version_code', $version->version_code) }}" placeholder="e.g., 1" required>
                            @error('version_code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror>
                            <p class="text-xs text-default-500 mt-1">Incremental number for version comparison</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="platform" class="form-label">Platform <span class="text-red-500">*</span></label>
                            <select name="platform" id="platform" class="form-select @error('platform') border-red-500 @enderror" required>
                                <option value="">Select Platform</option>
                                <option value="android" {{ old('platform', $version->platform) == 'android' ? 'selected' : '' }}>Android</option>
                                <option value="ios" {{ old('platform', $version->platform) == 'ios' ? 'selected' : '' }}>iOS</option>
                                <option value="both" {{ old('platform', $version->platform) == 'both' ? 'selected' : '' }}>Both</option>
                            </select>
                            @error('platform')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="release_date" class="form-label">Release Date</label>
                            <input type="datetime-local" name="release_date" id="release_date" class="form-input @error('release_date') border-red-500 @enderror" value="{{ old('release_date', $version->release_date?->format('Y-m-d\TH:i')) }}">
                            @error('release_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="minimum_version" class="form-label">Minimum Supported Version</label>
                            <input type="text" name="minimum_version" id="minimum_version" class="form-input @error('minimum_version') border-red-500 @enderror" value="{{ old('minimum_version', $version->minimum_version) }}" placeholder="e.g., 0.9.0">
                            @error('minimum_version')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="minimum_version_code" class="form-label">Minimum Version Code</label>
                            <input type="number" name="minimum_version_code" id="minimum_version_code" class="form-input @error('minimum_version_code') border-red-500 @enderror" value="{{ old('minimum_version_code', $version->minimum_version_code) }}" placeholder="e.g., 9">
                            @error('minimum_version_code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-default-500 mt-1">Versions below this will be forced to update</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="download_url" class="form-label">Download URL</label>
                        <input type="url" name="download_url" id="download_url" class="form-input @error('download_url') border-red-500 @enderror" value="{{ old('download_url', $version->download_url) }}" placeholder="https://play.google.com/store/apps/...">
                        @error('download_url')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="release_notes" class="form-label">Release Notes</label>
                        <textarea name="release_notes" id="release_notes" rows="4" class="form-input @error('release_notes') border-red-500 @enderror" placeholder="What's new in this version...">{{ old('release_notes', $version->release_notes) }}</textarea>
                        @error('release_notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">New Features</label>
                        <div id="features-container">
                            @if(old('features', $version->features))
                                @foreach(old('features', $version->features ?? []) as $feature)
                                    <div class="flex gap-2 mb-2">
                                        <input type="text" name="features[]" class="form-input" value="{{ $feature }}" placeholder="Add a new feature...">
                                        <button type="button" class="btn btn-sm bg-red-500 text-white" onclick="this.parentElement.remove()">
                                            <i class="size-4" data-lucide="minus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="features[]" class="form-input" placeholder="Add a new feature...">
                                <button type="button" class="btn btn-sm bg-green-500 text-white" onclick="addField('features')">
                                    <i class="size-4" data-lucide="plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Bug Fixes</label>
                        <div id="bugfixes-container">
                            @if(old('bug_fixes', $version->bug_fixes))
                                @foreach(old('bug_fixes', $version->bug_fixes ?? []) as $bugFix)
                                    <div class="flex gap-2 mb-2">
                                        <input type="text" name="bug_fixes[]" class="form-input" value="{{ $bugFix }}" placeholder="Add a bug fix...">
                                        <button type="button" class="btn btn-sm bg-red-500 text-white" onclick="this.parentElement.remove()">
                                            <i class="size-4" data-lucide="minus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="bug_fixes[]" class="form-input" placeholder="Add a bug fix...">
                                <button type="button" class="btn btn-sm bg-green-500 text-white" onclick="addField('bug_fixes')">
                                    <i class="size-4" data-lucide="plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="is_active" class="form-checkbox" value="1" {{ old('is_active', $version->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-label mb-0">Active</label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_latest" id="is_latest" class="form-checkbox" value="1" {{ old('is_latest', $version->is_latest) ? 'checked' : '' }}>
                            <label for="is_latest" class="form-label mb-0">Mark as Latest</label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="force_update" id="force_update" class="form-checkbox" value="1" {{ old('force_update', $version->force_update) ? 'checked' : '' }}>
                            <label for="force_update" class="form-label mb-0">Force Update</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" name="display_order" id="display_order" class="form-input @error('display_order') border-red-500 @enderror" value="{{ old('display_order', $version->display_order) }}">
                        @error('display_order')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="save"></i>Update Version
                        </button>
                        <a href="{{ route('app-versions.index') }}" class="btn bg-default-200 text-default-600">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addField(type) {
            const container = document.getElementById(`${type}-container`);
            const newField = document.createElement('div');
            newField.className = 'flex gap-2 mb-2';
            newField.innerHTML = `
                <input type="text" name="${type}[]" class="form-input" placeholder="Add ${type === 'features' ? 'a new feature' : 'a bug fix'}...">
                <button type="button" class="btn btn-sm bg-red-500 text-white" onclick="this.parentElement.remove()">
                    <i class="size-4" data-lucide="minus"></i>
                </button>
            `;
            container.appendChild(newField);
            lucide.createIcons();
        }
    </script>
@endsection
