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

    @if ($message = Session::get('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    {{-- Statistics Cards --}}
    @if(isset($stats))
    <div class="grid lg:grid-cols-5 md:grid-cols-3 grid-cols-2 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-primary/10 rounded-lg">
                        <i class="text-primary size-6" data-lucide="shopping-cart"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Total</p>
                        <h4 class="text-2xl font-bold">{{ $stats['total_purchases'] }}</h4>
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
                        <p class="text-sm text-default-600">Awaiting Check</p>
                        <h4 class="text-2xl font-bold">{{ $stats['awaiting_check'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-success/10 rounded-lg">
                        <i class="text-success size-6" data-lucide="trophy"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Won</p>
                        <h4 class="text-2xl font-bold">{{ $stats['won'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-danger/10 rounded-lg">
                        <i class="text-danger size-6" data-lucide="x-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Not Won</p>
                        <h4 class="text-2xl font-bold">{{ $stats['not_won'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-info/10 rounded-lg">
                        <i class="text-info size-6" data-lucide="check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-default-600">Approved</p>
                        <h4 class="text-2xl font-bold">{{ $stats['approved'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Actions --}}
    @can('lottery-check')
    <div class="card mb-4">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-3">
                <h6 class="text-lg font-semibold">Lottery Result Actions:</h6>
                <form action="{{ route('purchases.check-results') }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn bg-primary text-white" onclick="return confirm('Check all approved purchases against latest lottery results?')">
                        <i class="size-4" data-lucide="search"></i>
                        Check All Results
                    </button>
                </form>
                <form action="{{ route('purchases.notify-results') }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn bg-success text-white" onclick="return confirm('Send notifications to all customers with recently checked results?')">
                        <i class="size-4" data-lucide="send"></i>
                        Notify All Customers
                    </button>
                </form>
                <a href="{{ route('purchases.check-results-page') }}" class="btn bg-info text-white">
                    <i class="size-4" data-lucide="bar-chart"></i>
                    View Check Status
                </a>
            </div>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Purchases List</h6>
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('purchases.index') }}" class="flex gap-2 flex-wrap items-center">
                    <div class="relative" style="width: 12rem;">
                        <input 
                            class="ps-10 form-input form-input-sm w-full" 
                            placeholder="Search orders..." 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div>
                    
                    <div style="width: 9rem;">
                        <select name="status" class="form-input form-input-sm w-full">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="won" {{ request('status')=='won' ? 'selected' : '' }}>Won</option>
                            <option value="not_won" {{ request('status')=='not_won' ? 'selected' : '' }}>Not Won</option>
                        </select>
                    </div>

                    <div style="width: 8rem;">
                        <select name="result_status" class="form-input form-input-sm w-full">
                            <option value="">All Results</option>
                            <option value="unchecked" {{ request('result_status')=='unchecked' ? 'selected' : '' }}>Unchecked</option>
                            <option value="won" {{ request('result_status')=='won' ? 'selected' : '' }}>Winners</option>
                            <option value="not_won" {{ request('result_status')=='not_won' ? 'selected' : '' }}>Not Won</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="search"></i>Filter
                    </button>
                    @if(request('search') || request('status') || request('result_status'))
                        <a href="{{ route('purchases.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">
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
                                <th class="px-3.5 py-3 text-start" scope="col">Ticket Info</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Qty</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Total</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Status</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Result</th>
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
                                    <td class="px-3.5 py-3">
                                        <div>{{ $purchase->lotteryTicket->ticket_name ?? '-' }}</div>
                                        @if($purchase->lotteryTicket && $purchase->lotteryTicket->withdraw_date)
                                            <div class="text-xs text-primary font-medium mt-1">
                                                🎯 Draw: {{ \Carbon\Carbon::parse($purchase->lotteryTicket->withdraw_date)->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3">{{ $purchase->quantity }}</td>
                                    <td class="px-3.5 py-3">{{ number_format($purchase->total_price, 2) }}</td>
                                    <td class="px-3.5 py-3">
                                        @if($purchase->status=='pending')
                                            <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-xs">Pending</span>
                                        @elseif($purchase->status=='approved')
                                            <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">Approved</span>
                                        @elseif($purchase->status=='rejected')
                                            <span class="inline-flex px-2 py-1 bg-danger/10 text-danger rounded text-xs">Rejected</span>
                                        @elseif($purchase->status=='won')
                                            <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">🎉 WON</span>
                                        @elseif($purchase->status=='not_won')
                                            <span class="inline-flex px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Not Won</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3">
                                        @if($purchase->checked_at)
                                            @if($purchase->status == 'won')
                                                <span class="text-xs font-semibold text-success">{{ $purchase->prize_won }}</span>
                                            @else
                                                <span class="text-xs text-default-500">Checked</span>
                                            @endif
                                            @if($purchase->drawResult)
                                                <div class="text-xs text-default-400">
                                                    {{ \Carbon\Carbon::parse($purchase->drawResult->draw_date)->format('M d') }}
                                                </div>
                                            @endif
                                        @elseif($purchase->status == 'approved')
                                            <span class="text-xs text-warning">⏳ Pending</span>
                                        @else
                                            <span class="text-xs text-default-400">-</span>
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
                                    <td colspan="10" class="px-3.5 py-8 text-center text-default-500">
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