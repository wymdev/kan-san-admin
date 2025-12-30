@extends('layouts.vertical', ['title' => 'Secondary Tickets'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .ticket-number {
        font-family: 'Courier New', monospace;
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: 0.2em;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Ticket Management'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-primary/10 rounded-lg">
                        <i class="text-primary size-6" data-lucide="ticket"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total Tickets</p>
                        <h4 class="text-2xl font-bold">{{ $ticketStats['total'] ?? $tickets->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-success/10 rounded-lg">
                        <i class="text-success size-6" data-lucide="check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Sold Tickets</p>
                        <h4 class="text-2xl font-bold">{{ $ticketStats['sold'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-warning/10 rounded-lg">
                        <i class="text-warning size-6" data-lucide="clock"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Unsold Tickets</p>
                        <h4 class="text-2xl font-bold">{{ $ticketStats['unsold'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-info/10 rounded-lg">
                        <i class="text-info size-6" data-lucide="calendar"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Draw Dates</p>
                        <h4 class="text-2xl font-bold">{{ $drawDates->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h6 class="card-title">Secondary Tickets</h6>
            <div class="flex gap-2">
                <a href="{{ route('secondary-tickets.export', request()->query()) }}" class="btn btn-xs bg-success text-white">
                    <i class="size-4 me-1" data-lucide="download"></i>Export Excel
                </a>
                <a href="{{ route('secondary-tickets.create') }}" class="btn btn-xs bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="plus"></i>Add Ticket
                </a>
            </div>
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('secondary-tickets.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 12rem;">
                        <input 
                            class="ps-10 form-input form-input-sm w-full" 
                            placeholder="Search tickets..." 
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    
                    <div style="width: 9rem;">
                        <select name="withdraw_date" class="form-input form-input-sm w-full">
                            <option value="">All Dates</option>
                            @foreach($drawDates as $date)
                                <option value="{{ $date->format('Y-m-d') }}" {{ request('withdraw_date') == $date->format('Y-m-d') ? 'selected' : '' }}>
                                    {{ $date->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="width: 7rem;">
                        <select name="has_transactions" class="form-input form-input-sm w-full">
                            <option value="">All Status</option>
                            <option value="yes" {{ request('has_transactions') == 'yes' ? 'selected' : '' }}>Sold</option>
                            <option value="no" {{ request('has_transactions') == 'no' ? 'selected' : '' }}>Unsold</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    @if(request()->hasAny(['search', 'withdraw_date', 'has_transactions']))
                        <a href="{{ route('secondary-tickets.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-default-200">
                <thead class="bg-default-150">
                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                    <th class="px-3.5 py-3 text-start" scope="col">No</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Ticket Number</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Draw Date</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Price</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Source</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Transactions</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Created</th>
                    <th class="px-3.5 py-3 text-start" scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($tickets as $i => $ticket)
                    <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                        <td class="px-3.5 py-3">{{ $tickets->firstItem() + $i }}</td>
                        <td class="px-3.5 py-3">
                            <span class="ticket-number text-primary">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td class="px-3.5 py-3">
                            @if($ticket->withdraw_date)
                                <span class="text-primary font-medium">{{ $ticket->withdraw_date->format('M d, Y') }}</span>
                            @else
                                <span class="text-default-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3">
                            @if($ticket->price)
                                ฿{{ number_format($ticket->price, 2) }}
                            @else
                                <span class="text-default-400">-</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3">
                            {{ $ticket->source_seller ?? '-' }}
                        </td>
                        <td class="px-3.5 py-3">
                            @if($ticket->transactions->count() > 0)
                                <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">
                                    {{ $ticket->transactions->count() }} sale(s)
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-xs">
                                    Unsold
                                </span>
                            @endif
                        </td>
                        <td class="px-3.5 py-3 text-xs">{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-3.5 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('secondary-tickets.show', $ticket) }}" class="btn btn-sm bg-info/10 text-info">
                                    <i class="size-4" data-lucide="eye"></i>
                                </a>
                                <a href="{{ route('secondary-tickets.edit', $ticket) }}" class="btn btn-sm bg-primary/10 text-primary">
                                    <i class="size-4" data-lucide="edit"></i>
                                </a>
                                @if($ticket->transactions->count() === 0)
                                    <form action="{{ route('secondary-tickets.destroy', $ticket) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-danger/10 text-danger">
                                            <i class="size-4" data-lucide="trash"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('secondary-transactions.create', ['ticket_id' => $ticket->id]) }}" class="btn btn-sm bg-success/10 text-success" title="Sell Ticket">
                                    <i class="size-4" data-lucide="shopping-cart"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3.5 py-8 text-center text-default-500">
                            No secondary tickets found. <a href="{{ route('secondary-tickets.create') }}" class="text-primary hover:underline">Add your first ticket</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <p class="text-default-500 text-sm">Showing <b>{{ $tickets->count() }}</b> of <b>{{ $tickets->total() }}</b> Results</p>
            <nav class="flex items-center gap-2">
                {{ $tickets->links() }}
            </nav>
        </div>
    </div>
@endsection
