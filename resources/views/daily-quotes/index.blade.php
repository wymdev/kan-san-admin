@extends('layouts.vertical', ['title' => 'Daily Quotes'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Mobile App', 'title' => 'Daily Quotes'])

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header flex items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('daily-quotes.index') }}" class="flex gap-2 flex-wrap items-center">
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
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div style="width: 8rem;">
                        <select name="category" class="form-input form-input-sm w-full">
                            <option value="">All Categories</option>
                            <option value="motivation" {{ request('category') == 'motivation' ? 'selected' : '' }}>Motivation</option>
                            <option value="inspiration" {{ request('category') == 'inspiration' ? 'selected' : '' }}>Inspiration</option>
                            <option value="success" {{ request('category') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="luck" {{ request('category') == 'luck' ? 'selected' : '' }}>Luck</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    <a href="{{ route('daily-quotes.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <a href="{{ route('daily-quotes.create') }}" class="btn btn-xs bg-primary text-white ms-auto flex-shrink-0">
                    <i class="size-4 me-1" data-lucide="plus"></i>New Quote
                </a>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700">
                                    <th class="px-3.5 py-3 text-start">Quote</th>
                                    <th class="px-3.5 py-3 text-start">Author</th>
                                    <th class="px-3.5 py-3 text-start">Category</th>
                                    <th class="px-3.5 py-3 text-start">Status</th>
                                    <th class="px-3.5 py-3 text-start">Recipients</th>
                                    <th class="px-3.5 py-3 text-start">Scheduled</th>
                                    <th class="px-3.5 py-3 text-start">Sent At</th>
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                @forelse ($quotes as $quote)
                                    <tr class="text-default-800 font-normal text-sm hover:bg-default-100 transition">
                                        <td class="px-3.5 py-2.5 max-w-md">
                                            <div class="font-medium text-default-900">{{ Str::limit($quote->quote, 100) }}</div>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            {{ $quote->author ?? '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500 capitalize">
                                                {{ $quote->category }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            @if($quote->is_sent)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                                    <i class="size-3" data-lucide="check-circle"></i> Sent
                                                </span>
                                            @elseif($quote->is_active)
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-yellow-100 border border-yellow-200 text-yellow-600">
                                                    <i class="size-3" data-lucide="clock"></i> Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            {{ $quote->is_sent ? $quote->recipients_count : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            {{ $quote->scheduled_for ? $quote->scheduled_for->format('d M Y') : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            {{ $quote->sent_at ? $quote->sent_at->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="px-3.5 py-2.5 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                @if(!$quote->is_sent)
                                                    <form action="{{ route('daily-quotes.send-now', $quote) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-500 hover:text-green-600" title="Send Now" onclick="return confirm('Send this quote now?')">
                                                            <i class="size-4" data-lucide="send"></i>
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('daily-quotes.edit', $quote) }}" class="text-yellow-500 hover:text-yellow-600">
                                                        <i class="size-4" data-lucide="edit"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('daily-quotes.destroy', $quote) }}" method="POST" class="inline">
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
                                        <td colspan="8" class="text-center text-default-400 py-6">No quotes found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-default-500 text-sm">Showing <b>{{ $quotes->count() }}</b> of <b>{{ $quotes->total() }}</b> Results</p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $quotes->withQueryString()->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/components/timepicker.js'])
@endsection
