@extends('layouts.vertical', ['title' => 'View App Banner'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'View Banner'])

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium text-default-900 mb-2">Title</h3>
                        <p>{{ $banner->title }}</p>
                    </div>

                    <div>
                        <h3 class="font-medium text-default-900 mb-2">Type</h3>
                        <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500 capitalize">
                            {{ $banner->banner_type }}
                        </span>
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="font-medium text-default-900 mb-2">Description</h3>
                        <p>{{ $banner->description ?? 'No description' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="font-medium text-default-900 mb-2">Image</h3>
                        @if($banner->image_path)
                            <img src="{{ asset('storage/' . $banner->image_path) }}" alt="Banner" class="w-full max-w-md h-auto rounded object-cover">
                            <p class="text-xs text-gray-500 mt-1">Path: {{ $banner->image_path }}</p>
                        @else
                            <p class="text-gray-500">No image available</p>
                        @endif
                    </div>

                    @if($banner->action_url)
                        <div>
                            <h3 class="font-medium text-default-900 mb-2">Action URL</h3>
                            <p>{{ $banner->action_url }}</p>
                        </div>
                    @endif

                    @if($banner->action_type)
                        <div>
                            <h3 class="font-medium text-default-900 mb-2">Action Type</h3>
                            <p class="capitalize">{{ $banner->action_type }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="font-medium text-default-900 mb-2">Status</h3>
                        <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                            @if($banner->is_active) <i class="size-3" data-lucide="check-circle"></i> Active @else Inactive @endif
                        </span>
                    </div>

                    <div>
                        <h3 class="font-medium text-default-900 mb-2">Active Period</h3>
                        <p>{{ $banner->start_date ? $banner->start_date->format('d M Y') : 'No start' }} 
                        @if($banner->end_date) - {{ $banner->end_date->format('d M Y') }} @endif</p>
                    </div>

                    <div>
                        <h3 class="font-medium text-default-900 mb-2">Display Order</h3>
                        <p>{{ $banner->display_order ?? 'N/A' }}</p>
                    </div>

                    <div class="md:col-span-2 flex gap-2 mt-6">
                        <a href="{{ route('app-banners.edit', $banner) }}" class="btn bg-primary text-white">
                            <i class="size-4 me-1" data-lucide="edit"></i>Edit
                        </a>
                        <a href="{{ route('app-banners.index') }}" class="btn bg-default-200 text-default-600 hover:bg-default-300">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
