@extends('layouts.vertical', ['title' => 'App Versions'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'App Versions'])

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
                <form method="GET" action="{{ route('app-versions.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 10rem;">
                        <input 
                            name="search"
                            value="{{ request('search') }}"
                            class="ps-10 form-input form-input-sm w-full"
                            placeholder="Search version..."
                            type="text"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    <div style="width: 8rem;">
                        <select name="platform" class="form-input form-input-sm w-full">
                            <option value="">All Platforms</option>
                            <option value="android" {{ request('platform') == 'android' ? 'selected' : '' }}>Android</option>
                            <option value="ios" {{ request('platform') == 'ios' ? 'selected' : '' }}>iOS</option>
                            <option value="both" {{ request('platform') == 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    <a href="{{ route('app-versions.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <a href="{{ route('app-versions.create') }}" class="btn btn-xs bg-primary text-white ms-auto flex-shrink-0">
                    <i class="size-4 me-1" data-lucide="plus"></i>New Version
                </a>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700">
                                    <th class="px-3.5 py-3 text-start">Version</th>
                                    <th class="px-3.5 py-3 text-start">Code</th>
                                    <th class="px-3.5 py-3 text-start">Platform</th>
                                    <th class="px-3.5 py-3 text-start">Status</th>
                                    <th class="px-3.5 py-3 text-start">Force Update</th>
                                    <th class="px-3.5 py-3 text-start">Release Date</th>
                                    <th class="px-3.5 py-3 text-start">Latest</th>
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                @forelse ($versions as $version)
                                    <tr class="text-default-800 font-normal text-sm hover:bg-default-100 transition">
                                        <td class="px-3.5 py-2.5">
                                            <div class="font-medium text-default-900">{{ $version->version }}</div>
                                            <div class="text-xs text-default-500">{{ Str::limit($version->release_notes, 50) }}</div>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-mono bg-default-100 border border-default-200 text-default-700">
                                                {{ $version->version_code }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-blue-100 border border-blue-200 text-blue-600 capitalize">
                                                {{ $version->platform }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            @if($version->is_active)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                                    <i class="size-3" data-lucide="check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            @if($version->force_update)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-red-100 border border-red-200 text-red-600">
                                                    <i class="size-3" data-lucide="alert-circle"></i> Yes
                                                </span>
                                            @else
                                                <span class="text-default-400 text-xs">No</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-xs">
                                            {{ $version->release_date ? $version->release_date->format('d M Y') : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            @if($version->is_latest)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-purple-100 border border-purple-200 text-purple-600">
                                                    <i class="size-3" data-lucide="star"></i> Latest
                                                </span>
                                            @else
                                                <span class="text-default-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                <a href="{{ route('app-versions.show', $version) }}" class="text-blue-500 hover:text-blue-600" title="View">
                                                    <i class="size-4" data-lucide="eye"></i>
                                                </a>
                                                <a href="{{ route('app-versions.edit', $version) }}" class="text-yellow-500 hover:text-yellow-600" title="Edit">
                                                    <i class="size-4" data-lucide="edit"></i>
                                                </a>
                                                <form action="{{ route('app-versions.destroy', $version) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-600" onclick="return confirm('Are you sure you want to delete this version?')" title="Delete">
                                                        <i class="size-4" data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-default-400 py-6">No versions found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-default-500 text-sm">Showing <b>{{ $versions->count() }}</b> of <b>{{ $versions->total() }}</b> Results</p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $versions->withQueryString()->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
