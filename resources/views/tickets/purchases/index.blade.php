@extends('layouts.vertical', ['title' => 'Ticket Purchases'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Admin', 'title' => 'Ticket Purchases Management'])

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

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Purchases List</h6>
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('purchases.index') }}" class="w-full flex gap-3">
                    <div class="relative flex-1">
                        <input class="form-input form-input-sm ps-9 w-full" placeholder="Search for order number, customer..." type="text" name="search" value="{{ request('search') }}" />
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3">
                            <i class="size-3.5 flex items-center text-default-500 fill-default-100"
                            data-lucide="search"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm bg-primary text-white">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('purchases.index') }}" class="btn btn-sm bg-default-200 text-default-600 hover:bg-default-300">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                            <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                <th class="px-3.5 py-3 text-start" scope="col">No</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Order No</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Customer</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Ticket Name</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Qty</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Total</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Status</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Purchased At</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($purchases as $i => $purchase)
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">{{ $purchases->firstItem() + $i }}</td>
                                    <td class="px-3.5 py-3 font-mono">{{ $purchase->order_number }}</td>
                                    <td class="px-3.5 py-3">
                                        {{ $purchase->customer->full_name ?? '' }}<br>
                                        <span class="text-xs text-default-500">{{ $purchase->customer->phone_number ?? '' }}</span>
                                    </td>
                                    <td class="px-3.5 py-3">{{ $purchase->lotteryTicket->ticket_name ?? '-' }}</td>
                                    <td class="px-3.5 py-3">{{ $purchase->quantity }}</td>
                                    <td class="px-3.5 py-3">{{ number_format($purchase->total_price, 2) }}</td>
                                    <td class="px-3.5 py-3">
                                        @if($purchase->status=='pending')
                                            <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-xs">Pending</span>
                                        @elseif($purchase->status=='approved')
                                            <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">Approved</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 bg-danger/10 text-danger rounded text-xs">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3 text-xs">{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-3.5 py-3">
                                        <a class="btn btn-sm bg-primary/10 text-primary" href="{{ route('purchases.show', $purchase->id) }}">
                                            <i class="size-4" data-lucide="eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3.5 py-8 text-center text-default-500">
                                        No purchases found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p class="text-default-500 text-sm">Showing <b>{{ $purchases->count() }}</b> of <b>{{ $purchases->total() }}</b> Results</p>
                <nav aria-label="Pagination" class="flex items-center gap-2">
                    {{ $purchases->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection
