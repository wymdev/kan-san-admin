@extends('layouts.vertical', ['title' => 'Announcements'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Announcements'])

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header flex items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('announcements.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 10rem;">
                        <input 
                            name="search"
                            value="{{ request('search') }}"
                            class="ps-10 form-input form-input-sm w-full"
                            placeholder="Search..."
                            type="text"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    <div style="width: 8rem;">
                        <select name="status" class="form-input form-input-sm w-full">
                            <option value="">All Status</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    <a href="{{ route('announcements.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <a href="{{ route('announcements.create') }}" class="btn btn-xs bg-primary text-white ms-auto flex-shrink-0">
                    <i class="size-4 me-1" data-lucide="plus"></i>New Announcement
                </a>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700">
                                    <th class="px-3.5 py-3 text-start">Title</th>
                                    <th class="px-3.5 py-3 text-start">Type</th>
                                    <th class="px-3.5 py-3 text-start">Status</th>
                                    <th class="px-3.5 py-3 text-start">Recipients</th>
                                    <th class="px-3.5 py-3 text-start">Scheduled</th>
                                    <th class="px-3.5 py-3 text-start">Sent At</th>
                                    <th class="px-3.5 py-3 text-start">Created By</th>
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                @forelse ($announcements as $announcement)
                                    <tr class="text-default-800 font-normal text-sm whitespace-nowrap hover:bg-default-100 transition">
                                        <td class="px-3.5 py-2.5">
                                            <div class="font-medium text-default-900">{{ $announcement->title }}</div>
                                            <div class="text-xs text-default-500 mt-0.5">{{ Str::limit($announcement->body, 60) }}</div>
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal
                                                @if($announcement->type === 'general') bg-blue-100 border border-blue-200 text-blue-600
                                                @elseif($announcement->type === 'promotion') bg-green-100 border border-green-200 text-green-600
                                                @elseif($announcement->type === 'maintenance') bg-orange-100 border border-orange-200 text-orange-600
                                                @else bg-purple-100 border border-purple-200 text-purple-600
                                                @endif">
                                                {{ ucfirst($announcement->type) }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            @if($announcement->is_sent)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                                    <i class="size-3" data-lucide="check-circle"></i> Sent
                                                </span>
                                            @elseif($announcement->is_published)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-yellow-100 border border-yellow-200 text-yellow-600">
                                                    <i class="size-3" data-lucide="clock"></i> Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                    <i class="size-3" data-lucide="file-text"></i> Draft
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            @if($announcement->is_sent)
                                                <div class="text-sm">{{ $announcement->recipients_count }}</div>
                                                <div class="text-xs text-default-500">
                                                    <span class="text-green-600">✓ {{ $announcement->success_count }}</span>
                                                    @if($announcement->failed_count > 0)
                                                        <span class="text-red-600">✗ {{ $announcement->failed_count }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-default-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            {{ $announcement->scheduled_at ? $announcement->scheduled_at->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            {{ $announcement->sent_at ? $announcement->sent_at->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            {{ $announcement->creator->name ?? '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            <div class="flex gap-2">
                                                <a href="{{ route('announcements.show', $announcement) }}" class="text-blue-500 hover:text-blue-600">
                                                    <i class="size-4" data-lucide="eye"></i>
                                                </a>
                                                @if(!$announcement->is_sent)
                                                    <a href="{{ route('announcements.edit', $announcement) }}" class="text-yellow-500 hover:text-yellow-600">
                                                        <i class="size-4" data-lucide="edit"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-600" onclick="return confirm('Are you sure?')">
                                                        <i class="size-4" data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-default-400 py-6">No announcements found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-default-500 text-sm">Showing <b>{{ $announcements->count() }}</b> of <b>{{ $announcements->total() }}</b> Results</p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $announcements->withQueryString()->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/timepicker.js'])
@endsection
