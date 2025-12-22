@extends('layouts.vertical', ['title' => 'Ticket Details'])

@section('css')
<style>
    .ticket-number-large {
        font-family: 'Courier New', monospace;
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: 0.4em;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Secondary Sales', 'title' => 'Ticket Details'])

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Ticket Info --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h6 class="card-title">Ticket Information</h6>
                    <div class="flex gap-2">
                        <a href="{{ route('secondary-tickets.edit', $secondaryTicket) }}" class="btn btn-sm bg-primary/10 text-primary">
                            <i class="size-4 me-1" data-lucide="edit"></i> Edit
                        </a>
                        <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" class="btn btn-sm bg-success text-white">
                            <i class="size-4 me-1" data-lucide="shopping-cart"></i> Sell
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center py-6 bg-gradient-to-r from-primary/5 to-success/5 rounded-xl mb-6">
                        <p class="text-sm text-default-500 mb-2">Ticket Number</p>
                        <div class="ticket-number-large text-primary">{{ $secondaryTicket->ticket_number }}</div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-default-500">Draw Date</p>
                            <p class="font-medium">
                                @if($secondaryTicket->withdraw_date)
                                    {{ $secondaryTicket->withdraw_date->format('F d, Y') }}
                                @else
                                    <span class="text-default-400">Not set</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Price</p>
                            <p class="font-medium">
                                @if($secondaryTicket->price)
                                    ฿{{ number_format($secondaryTicket->price, 2) }}
                                @else
                                    <span class="text-default-400">Not set</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Ticket Type</p>
                            <p class="font-medium capitalize">{{ $secondaryTicket->ticket_type ?? 'Normal' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Source Seller</p>
                            <p class="font-medium">{{ $secondaryTicket->source_seller ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Created By</p>
                            <p class="font-medium">{{ $secondaryTicket->createdBy?->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Created At</p>
                            <p class="font-medium">{{ $secondaryTicket->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($secondaryTicket->notes)
                        <div class="mt-4 p-4 bg-default-100 rounded-lg">
                            <p class="text-sm text-default-500">Notes</p>
                            <p>{{ $secondaryTicket->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transactions --}}
            <div class="card mt-6">
                <div class="card-header">
                    <h6 class="card-title">Sales Transactions ({{ $secondaryTicket->transactions->count() }})</h6>
                </div>
                @if($secondaryTicket->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                            <tr class="text-sm font-normal text-default-700">
                                <th class="px-3.5 py-3 text-start">Transaction #</th>
                                <th class="px-3.5 py-3 text-start">Customer</th>
                                <th class="px-3.5 py-3 text-start">Amount</th>
                                <th class="px-3.5 py-3 text-start">Status</th>
                                <th class="px-3.5 py-3 text-start">Paid</th>
                                <th class="px-3.5 py-3 text-start">Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($secondaryTicket->transactions as $transaction)
                                <tr class="text-sm">
                                    <td class="px-3.5 py-3 font-mono">
                                        <a href="{{ route('secondary-transactions.show', $transaction) }}" class="text-primary hover:underline">
                                            {{ $transaction->transaction_number }}
                                        </a>
                                    </td>
                                    <td class="px-3.5 py-3">{{ $transaction->customer_display_name }}</td>
                                    <td class="px-3.5 py-3">฿{{ number_format($transaction->amount, 2) }}</td>
                                    <td class="px-3.5 py-3">
                                        @if($transaction->status == 'won')
                                            <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">🎉 WON</span>
                                        @elseif($transaction->status == 'not_won')
                                            <span class="inline-flex px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Not Won</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 bg-warning/10 text-warning rounded text-xs">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3">
                                        @if($transaction->is_paid)
                                            <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">Paid</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 bg-danger/10 text-danger rounded text-xs">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3 text-xs">{{ $transaction->purchased_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-body text-center text-default-500">
                        No sales yet. <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" class="text-primary hover:underline">Create a sale</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            @if($secondaryTicket->source_image)
                <div class="card mb-6">
                    <div class="card-header">
                        <h6 class="card-title">Ticket Image</h6>
                    </div>
                    <div class="card-body">
                        <img src="{{ Storage::url($secondaryTicket->source_image) }}" alt="Ticket Image" class="w-full rounded-lg">
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Quick Actions</h6>
                </div>
                <div class="card-body space-y-3">
                    <a href="{{ route('secondary-transactions.create', ['ticket_id' => $secondaryTicket->id]) }}" class="btn bg-success text-white w-full">
                        <i class="size-4 me-1" data-lucide="shopping-cart"></i> Create Sale
                    </a>
                    <a href="{{ route('secondary-tickets.edit', $secondaryTicket) }}" class="btn bg-primary/10 text-primary w-full">
                        <i class="size-4 me-1" data-lucide="edit"></i> Edit Ticket
                    </a>
                    @if($secondaryTicket->transactions->count() === 0)
                        <form action="{{ route('secondary-tickets.destroy', $secondaryTicket) }}" method="POST" onsubmit="return confirm('Delete this ticket?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn bg-danger/10 text-danger w-full">
                                <i class="size-4 me-1" data-lucide="trash"></i> Delete Ticket
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
