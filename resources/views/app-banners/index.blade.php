@extends('layouts.vertical', ['title' => 'App Banners'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'App Banners'])

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header flex items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('app-banners.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 10rem;">
                        <input 
                            name="search"
                            value="{{ request('search') }}"
                            class="ps-10 form-input form-input-sm w-full"
                            placeholder="Search title/description..."
                            type="text"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    <div style="width: 8rem;">
                        <select name="type" class="form-input form-input-sm w-full">
                            <option value="">All Types</option>
                            <option value="news" {{ request('type') == 'news' ? 'selected' : '' }}>News</option>
                            <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>Promotion</option>
                            <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Announcement</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    <a href="{{ route('app-banners.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <a href="{{ route('app-banners.create') }}" class="btn btn-xs bg-primary text-white ms-auto flex-shrink-0">
                    <i class="size-4 me-1" data-lucide="plus"></i>New Banner
                </a>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700">
                                    <th class="px-3.5 py-3 text-start">Image</th>
                                    <th class="px-3.5 py-3 text-start">Title</th>
                                    <th class="px-3.5 py-3 text-start">Type</th>
                                    <th class="px-3.5 py-3 text-start">Status</th>
                                    <th class="px-3.5 py-3 text-start">Active Period</th>
                                    <th class="px-3.5 py-3 text-start">Order</th>
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                @forelse ($banners as $banner)
                                    <tr class="text-default-800 font-normal text-sm hover:bg-default-100 transition">
                                        <td class="px-3.5 py-2.5">
                                            @if($banner->image_path)
                                                <img src="{{ asset('storage/' . $banner->image_path) }}" alt="Banner" alt="Banner" class="w-16 h-12 object-cover rounded">
                                            @else
                                                <span class="text-default-400">No Image</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 max-w-md">
                                            <div class="font-medium text-default-900">{{ Str::limit($banner->title, 50) }}</div>
                                            <div class="text-xs text-default-500">{{ Str::limit($banner->description, 100) }}</div>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500 capitalize">
                                                {{ $banner->banner_type }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            @if($banner->is_active)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                                    <i class="size-3" data-lucide="check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-xs">
                                            {{ $banner->start_date ? $banner->start_date->format('d M Y') : 'No start' }} 
                                            @if($banner->end_date) - {{ $banner->end_date->format('d M Y') }} @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            {{ $banner->display_order ?? '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                <a href="{{ route('app-banners.show', $banner) }}" class="text-blue-500 hover:text-blue-600">
                                                    <i class="size-4" data-lucide="eye"></i>
                                                </a>
                                                <a href="{{ route('app-banners.edit', $banner) }}" class="text-yellow-500 hover:text-yellow-600">
                                                    <i class="size-4" data-lucide="edit"></i>
                                                </a>
                                                <form action="{{ route('app-banners.destroy', $banner) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-600" onclick="return confirm('Are you sure you want to delete this banner?')">
                                                        <i class="size-4" data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-default-400 py-6">No banners found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-default-500 text-sm">Showing <b>{{ $banners->count() }}</b> of <b>{{ $banners->total() }}</b> Results</p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $banners->withQueryString()->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
