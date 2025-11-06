@extends('layouts.vertical', ['title' => 'Lottery Ticket List'])

@section('css')
<!-- Add any page-specific CSS here -->
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Ticket List'])

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header flex items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('tickets.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 10rem;">
                        <input 
                            name="search"
                            value="{{ request('search') }}"
                            class="ps-10 form-input form-input-sm w-full"
                            placeholder="Search tickets..."
                            type="text"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    <div style="width: 8.5rem;">
                        <input 
                            class="form-input form-input-sm w-full"
                            name="withdraw_date"
                            value="{{ request('withdraw_date') }}"
                            data-date-format="d M, Y" data-provider="flatpickr"
                            placeholder="Withdraw Date"
                            autocomplete="off"
                        />
                    </div>
                    <div style="width: 7rem;">
                        <select name="ticket_type" class="form-input form-input-sm w-full">
                            <option value="">All Types</option>
                            <option value="normal" {{ request('ticket_type') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="special" {{ request('ticket_type') == 'special' ? 'selected' : '' }}>Special</option>
                            <option value="lucky" {{ request('ticket_type') == 'lucky' ? 'selected' : '' }}>Lucky</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <a href="{{ route('tickets.create') }}" class="btn btn-xs bg-primary text-white ms-auto flex-shrink-0">
                    <i class="size-4 me-1" data-lucide="plus"></i>Add Ticket
                </a>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700">
                                    <th class="px-3.5 py-3 text-start">Barcode</th>
                                    <th class="px-3.5 py-3 text-start">Name</th>
                                    <th class="px-3.5 py-3 text-start">Type</th>
                                    <th class="px-3.5 py-3 text-start">Signature</th>
                                    <th class="px-3.5 py-3 text-start">Numbers</th>
                                    <th class="px-3.5 py-3 text-start">Period</th>
                                    <th class="px-3.5 py-3 text-start">Withdraw Date</th>
                                    <th class="px-3.5 py-3 text-start">Price</th>
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                @forelse ($tickets as $ticket)
                                    <tr 
                                        class="text-default-800 font-normal text-sm whitespace-nowrap cursor-pointer hover:bg-default-100 transition"
                                        data-href="{{ route('tickets.show', $ticket) }}">
                                        <td class="px-3.5 py-2.5 text-primary font-mono">{{ $ticket->bar_code }}</td>
                                        <td class="px-3.5 py-2.5">{{ $ticket->ticket_name }}</td>
                                        <td class="px-3.5 py-2.5 capitalize">
                                            <span class="inline-flex py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                {{ ucfirst($ticket->ticket_type) }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5">{{ $ticket->signature }}</td>
                                        <td class="px-3.5 py-2.5 font-mono">
                                            {{ is_array($ticket->numbers) ? implode(' ', $ticket->numbers) : $ticket->numbers }}
                                        </td>
                                        <td class="px-3.5 py-2.5">{{ $ticket->period }}</td>
                                        <td class="px-3.5 py-2.5">{{ $ticket->withdraw_date ? $ticket->withdraw_date->format('d M Y') : '-' }}</td>
                                        <td class="px-3.5 py-2.5">{{ number_format($ticket->price, 2) }}</td>
                                        <td class="px-3.5 py-2.5">
                                            <div class="hs-dropdown relative inline-flex">
                                                <!-- dropdown, keep for actions -->
                                                ...
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-default-400 py-6">No tickets found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-default-500 text-sm">Showing <b>{{ $tickets->count() }}</b> of <b>{{ $tickets->total() }}</b> Results</p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $tickets->withQueryString()->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('tr[data-href]').forEach(row => {
                row.addEventListener('click', function(e) {
                    // prevent from clicking inside buttons or dropdown
                    if(
                        e.target.closest('a') ||
                        e.target.closest('button') ||
                        e.target.closest('form')
                    ) return;
                    window.location = this.getAttribute('data-href');
                });
            });
        });
    </script>
    @vite(['resources/js/components/timepicker.js'])
@endsection
