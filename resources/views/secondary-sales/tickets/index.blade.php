@extends('layouts.vertical', ['title' => 'Secondary Tickets'])

@section('css')
<style>
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .dark .stat-card {
        background: rgb(31, 41, 55);
        border-color: rgb(55, 65, 81);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .dark .stat-label {
        color: #9ca3af;
    }
    .ticket-number {
        font-family: 'Courier New', monospace;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: 0.15em;
    }
    .table-container {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .dark .table-container {
        border-color: rgb(55, 65, 81);
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }
    .filter-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    @media (max-width: 640px) {
        .filter-section {
            grid-template-columns: 1fr 1fr;
        }
        .filter-section .full-width {
            grid-column: span 2;
        }
        .stat-value {
            font-size: 1.25rem;
        }
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Ticket Management'])

    {{-- Alert Messages --}}
    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <i class="size-5" data-lucide="check-circle"></i>
            <span>{{ $message }}</span>
        </div>
    @endif
    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <i class="size-5" data-lucide="alert-circle"></i>
            <span>{{ $message }}</span>
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/30">
                    <i class="text-indigo-600 dark:text-indigo-400 size-5" data-lucide="ticket"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Total Tickets</p>
                    <p class="stat-value text-gray-900 dark:text-white">{{ $ticketStats['total'] ?? $tickets->total() }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                    <i class="text-emerald-600 dark:text-emerald-400 size-5" data-lucide="check-circle"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Sold</p>
                    <p class="stat-value text-emerald-600 dark:text-emerald-400">{{ $ticketStats['sold'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-amber-100 dark:bg-amber-900/30">
                    <i class="text-amber-600 dark:text-amber-400 size-5" data-lucide="clock"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Unsold</p>
                    <p class="stat-value text-amber-600 dark:text-amber-400">{{ $ticketStats['unsold'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon bg-cyan-100 dark:bg-cyan-900/30">
                    <i class="text-cyan-600 dark:text-cyan-400 size-5" data-lucide="calendar"></i>
                </div>
                <div class="min-w-0">
                    <p class="stat-label">Draw Dates</p>
                    <p class="stat-value text-gray-900 dark:text-white">{{ $drawDates->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="table-container bg-white dark:bg-gray-800">
        {{-- Table Header --}}
        <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h6 class="text-lg font-semibold text-gray-900 dark:text-white">Secondary Tickets</h6>
                    <span class="badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $tickets->total() }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('secondary-tickets.export', request()->query()) }}" class="btn btn-sm bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg inline-flex items-center gap-1.5">
                        <i class="size-4" data-lucide="download"></i>
                        <span class="hidden sm:inline">Export Excel</span>
                    </a>
                    <a href="{{ route('secondary-tickets.create') }}" class="btn btn-sm bg-primary text-white rounded-lg inline-flex items-center gap-1.5">
                        <i class="size-4" data-lucide="plus"></i>
                        <span class="hidden sm:inline">Add Ticket</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('secondary-tickets.index') }}">
                <div class="filter-section">
                    <div class="full-width">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="form-input form-input-sm w-full pl-9 rounded-lg" placeholder="Search ticket number...">
                            <i class="size-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" data-lucide="search"></i>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Draw Date</label>
                        <select name="withdraw_date" class="form-select form-select-sm w-full rounded-lg">
                            <option value="">All Dates</option>
                            @foreach($drawDates as $date)
                                <option value="{{ $date->format('Y-m-d') }}" {{ request('withdraw_date') == $date->format('Y-m-d') ? 'selected' : '' }}>
                                    {{ $date->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status</label>
                        <select name="has_transactions" class="form-select form-select-sm w-full rounded-lg">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_transactions') == 'yes' ? 'selected' : '' }}>Sold</option>
                            <option value="no" {{ request('has_transactions') == 'no' ? 'selected' : '' }}>Unsold</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-sm bg-primary text-white rounded-lg flex-1">
                            <i class="size-4" data-lucide="filter"></i>
                        </button>
                        @if(request()->hasAny(['search', 'withdraw_date', 'has_transactions']))
                            <a href="{{ route('secondary-tickets.index') }}" class="btn btn-sm bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                                <i class="size-4" data-lucide="x"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Ticket Number</th>
                        <th class="px-4 py-3 text-left">Draw Date</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Source</th>
                        <th class="px-4 py-3 text-left">Sales</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tickets as $i => $ticket)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $tickets->firstItem() + $i }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="ticket-number text-primary">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($ticket->withdraw_date)
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->withdraw_date->format('M d, Y') }}</span>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                @if($ticket->price)
                                    ฿{{ number_format($ticket->price, 2) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $ticket->source_seller ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($ticket->transactions->count() > 0)
                                    <span class="badge bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                        {{ $ticket->transactions->count() }} sale(s)
                                    </span>
                                @else
                                    <span class="badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                        Unsold
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $ticket->created_at->format('M d, Y') }}<br>
                                <span class="text-gray-400">{{ $ticket->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('secondary-tickets.show', $ticket) }}" class="action-btn bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-100 dark:hover:bg-cyan-900/40">
                                        <i class="size-4" data-lucide="eye"></i>
                                    </a>
                                    <a href="{{ route('secondary-tickets.edit', $ticket) }}" class="action-btn bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40">
                                        <i class="size-4" data-lucide="edit"></i>
                                    </a>
                                    @if($ticket->transactions->count() === 0)
                                        <form action="{{ route('secondary-tickets.destroy', $ticket) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40">
                                                <i class="size-4" data-lucide="trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('secondary-transactions.create', ['ticket_id' => $ticket->id]) }}" class="action-btn bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40" title="Sell Ticket">
                                        <i class="size-4" data-lucide="shopping-cart"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="size-8 text-gray-400" data-lucide="ticket"></i>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 mb-2">No secondary tickets found</p>
                                    <a href="{{ route('secondary-tickets.create') }}" class="text-primary hover:underline text-sm">
                                        Add your first ticket →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium">{{ $tickets->firstItem() }}</span> to <span class="font-medium">{{ $tickets->lastItem() }}</span> of <span class="font-medium">{{ $tickets->total() }}</span> results
            </p>
            <nav class="flex items-center gap-1">
                {{ $tickets->links() }}
            </nav>
        </div>
        @endif
    </div>
@endsection
