@extends('layouts.vertical', ['title' => 'Edit App Banner'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Edit Banner'])

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <form action="{{ route('app-banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ $banner->title }}" class="form-input @error('title') border-red-500 @enderror" required maxlength="255" />
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Banner Type <span class="text-red-500">*</span></label>
                            <select name="banner_type" class="form-input @error('banner_type') border-red-500 @enderror" required>
                                <option value="">Select Type</option>
                                <option value="news" {{ $banner->banner_type == 'news' ? 'selected' : '' }}>News</option>
                                <option value="promotion" {{ $banner->banner_type == 'promotion' ? 'selected' : '' }}>Promotion</option>
                                <option value="announcement" {{ $banner->banner_type == 'announcement' ? 'selected' : '' }}>Announcement</option>
                            </select>
                            @error('banner_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-input @error('description') border-red-500 @enderror">{{ $banner->description }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Image (leave empty to keep current)</label>
                            <input type="file" name="image" id="image" accept="image/*" class="form-input @error('image') border-red-500 @enderror" onchange="previewImage(this)" />
                            @error('image')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <div id="imagePreview" class="mt-2 w-48 h-32 bg-gray-200 rounded flex items-center justify-center border-2 border-dashed border-gray-300 overflow-hidden">
                                @if($banner->image_path)
                                    <img src="{{ asset('storage/' . $banner->image_path) }}" alt="Current Banner" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-500">No current image</span>
                                @endif
                            </div>
                            @if($banner->image_path)
                                <p class="text-xs text-gray-500 mt-1">Current: {{ basename($banner->image_path) }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="form-label">Action URL (optional)</label>
                            <input type="url" name="action_url" value="{{ $banner->action_url }}" class="form-input @error('action_url') border-red-500 @enderror" />
                            @error('action_url')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Action Type (optional)</label>
                            <select name="action_type" class="form-input @error('action_type') border-red-500 @enderror">
                                <option value="">Select Type</option>
                                <option value="internal" {{ $banner->action_type == 'internal' ? 'selected' : '' }}>Internal</option>
                                <option value="external" {{ $banner->action_type == 'external' ? 'selected' : '' }}>External</option>
                                <option value="deeplink" {{ $banner->action_type == 'deeplink' ? 'selected' : '' }}>Deeplink</option>
                            </select>
                            @error('action_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Is Active</label>
                            <input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }} class="form-checkbox" />
                        </div>

                        <div>
                            <label class="form-label">Start Date (optional)</label>
                            <input type="date" name="start_date" value="{{ $banner->start_date ? $banner->start_date->format('Y-m-d') : '' }}" class="form-input @error('start_date') border-red-500 @enderror" />
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">End Date (optional)</label>
                            <input type="date" name="end_date" value="{{ $banner->end_date ? $banner->end_date->format('Y-m-d') : '' }}" class="form-input @error('end_date') border-red-500 @enderror" />
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" value="{{ $banner->display_order ?? 0 }}" min="0" class="form-input @error('display_order') border-red-500 @enderror" />
                            @error('display_order')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="save"></i>Update Banner
                        </button>
                        <a href="{{ route('app-banners.index') }}" class="btn bg-default-200 text-default-600 hover:bg-default-300">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-full h-full object-cover rounded">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
