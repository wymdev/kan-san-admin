@extends('layouts.vertical', ['title' => 'Purchase Detail'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials.page-title', [
        'subtitle' => 'Admin',
        'title' => 'Purchase Detail'
    ])

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="col-span-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Purchase #{{ $purchase->order_number }}</h6>
                </div>
                <div class="card-body space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="font-semibold text-default-800 mb-2">Customer Info</h5>
                            <div>
                                <div><b>Name:</b> {{ $purchase->customer->full_name ?? '-' }}</div>
                                <div><b>Phone:</b> {{ $purchase->customer->phone_number ?? '-' }}</div>
                                <div><b>Email:</b> {{ $purchase->customer->email ?? '-' }}</div>
                            </div>
                        </div>
                        <div>
                            <h5 class="font-semibold text-default-800 mb-2">Ticket Info</h5>
                            <div>
                                <div><b>Name:</b> {{ $purchase->lotteryTicket->ticket_name ?? '-' }}</div>
                                <div><b>Type:</b> {{ $purchase->lotteryTicket->ticket_type ?? '-' }}</div>
                                <div><b>Bar Code:</b> {{ $purchase->lotteryTicket->bar_code ?? '-' }}</div>
                                <div><b>Numbers:</b> 
                                    {{ is_array($purchase->lotteryTicket->numbers) 
                                        ? implode('-', $purchase->lotteryTicket->numbers) 
                                        : ($purchase->lotteryTicket->numbers ?? '-') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="font-semibold text-default-800 mb-2">Order Info</h5>
                            <div><b>Order No:</b> {{ $purchase->order_number }}</div>
                            <div><b>Quantity:</b> {{ $purchase->quantity }}</div>
                            <div><b>Total Price:</b> {{ number_format($purchase->total_price, 2) }} MMK</div>
                            <div><b>Status:</b>
                                @if($purchase->status=='pending')
                                    <span class="px-2 py-1 bg-warning/10 text-warning rounded text-xs">Pending</span>
                                @elseif($purchase->status=='approved')
                                    <span class="px-2 py-1 bg-success/10 text-success rounded text-xs">Approved</span>
                                @else
                                    <span class="px-2 py-1 bg-danger/10 text-danger rounded text-xs">Rejected</span>
                                @endif
                            </div>
                            <div><b>Purchased at:</b> {{ $purchase->created_at->format('M d, Y H:i') }}</div>
                            @if($purchase->approved_at)
                              <div><b>Processed at:</b> {{ $purchase->approved_at->format('M d, Y H:i') }}</div>
                            @endif
                        </div>
                        <div>
                            <h5 class="font-semibold text-default-800 mb-2">Payment Proof</h5>
                            @if($purchase->payment_screenshot)
                                <a href="{{ asset('storage/'.$purchase->payment_screenshot) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$purchase->payment_screenshot) }}" 
                                         style="max-width:270px; border-radius:10px; margin-bottom:7px;">
                                </a>
                                <p><a class="text-primary hover:underline" target="_blank" href="{{ asset('storage/'.$purchase->payment_screenshot) }}">Open Full Image</a></p>
                            @else
                                <p class="text-danger">No Screenshot Uploaded</p>
                            @endif
                        </div>
                    </div>
                    @if($purchase->status=='rejected' && $purchase->rejection_reason)
                        <div>
                            <h5 class="font-semibold text-danger">Rejection Reason</h5>
                            <div class="bg-danger/5 border border-danger/50 text-danger rounded p-3">
                                {{ $purchase->rejection_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions</h5>
                </div>
                <!-- Modern Approve & Reject Section -->
                <div class="card-body space-y-6">
                    @if($purchase->status == 'pending')
                        @can('payment-approve')
                            <div class="flex flex-col gap-3">
                                {{-- Approve --}}
                                <form action="{{ route('purchases.approve', $purchase->id) }}" method="post">
                                    @csrf
                                    <button type="submit"
                                            class="btn w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white shadow"
                                            onclick="return confirm('Approve this purchase?')">
                                        <i class="size-4" data-lucide="check-circle"></i>
                                        <span class="font-semibold">Approve</span>
                                    </button>
                                </form>
                                {{-- Reject --}}
                                <form action="{{ route('purchases.reject', $purchase->id) }}" method="post" class="flex flex-col gap-2 mt-2">
                                    @csrf
                                    <textarea required name="rejection_reason"
                                              class="form-input w-full border-red-400 focus:border-red-600 focus:ring-red-300"
                                              rows="2" placeholder="Why rejected?"></textarea>
                                    <button type="submit"
                                        style="background-color: #ef4444 !important; color: #fff !important;"
                                        class="w-full inline-flex items-center justify-center gap-2 shadow font-semibold py-2 rounded"
                                        onclick="return confirm('Confirm reject?')">
                                        <i class="size-4" data-lucide="x-circle"></i>
                                        <span>Reject</span>
                                    </button>
                                </form>
                            </div>
                            <div class="mt-4">
                                <span class="inline-flex items-center gap-2 bg-warning/10 text-warning border border-warning/20 rounded px-3 py-2 text-xs font-semibold">
                                    <i data-lucide="alert-triangle" class="size-4"></i>
                                    Pending admin action: Please approve or reject this payment.
                                </span>
                            </div>
                        @endcan
                    @elseif($purchase->status == 'approved')
                        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded text-center font-semibold">
                            <i class="size-5" data-lucide="check-circle"></i> Purchase Approved!
                        </div>
                    @elseif($purchase->status == 'rejected')
                        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded text-center font-semibold">
                            <i class="size-5" data-lucide="x-circle"></i> Purchase Rejected
                        </div>
                        @if($purchase->rejection_reason)
                            <div class="mt-3 bg-red-50 border-l-4 border-red-400 text-red-700 p-3 rounded">
                                <b>Rejection Reason:</b>
                                <div>{{ $purchase->rejection_reason }}</div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
