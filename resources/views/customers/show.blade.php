@extends('layouts.vertical', ['title' => 'Customer Details'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Customer Details'])

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <div class="card max-w-2xl mx-auto">
        <div class="card-header flex justify-between items-center">
            <h6 class="card-title">Customer Information</h6>
            @if($customer->is_blocked)
                <span class="inline-flex px-3 py-1 bg-danger text-white rounded-full text-sm font-bold">
                    <i class="size-4 mr-1" data-lucide="shield-alert"></i> BLOCKED
                </span>
            @else
                <span class="inline-flex px-3 py-1 bg-success/10 text-success rounded-full text-sm font-medium">
                    <i class="size-4 mr-1" data-lucide="check-circle"></i> Active
                </span>
            @endif
        </div>
        <div class="card-body space-y-6">
            @if($customer->is_blocked)
                <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded">
                    <p class="font-semibold mb-1"><i class="size-4" data-lucide="alert-triangle"></i> Account Blocked</p>
                    <p class="text-sm"><b>Reason:</b> {{ $customer->block_reason ?? 'No reason provided' }}</p>
                    <p class="text-sm"><b>Blocked at:</b> {{ $customer->blocked_at?->format('M d, Y H:i') }}</p>
                    @if($customer->blockedByUser)
                        <p class="text-sm"><b>Blocked by:</b> {{ $customer->blockedByUser->name }}</p>
                    @endif
                </div>
            @endif
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-default-500 text-sm mb-1">Phone Number</p>
                    <p class="text-default-900 font-mono font-semibold">{{ $customer->phone_number }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Full Name</p>
                    <p class="text-default-900 font-semibold">{{ $customer->full_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Email</p>
                    <p class="text-default-900">{{ $customer->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Gender</p>
                    <p class="text-default-900">
                        {{ $customer->gender == 'M' ? 'Male' : ($customer->gender == 'F' ? 'Female' : ($customer->gender ?? 'N/A')) }}
                    </p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Date of Birth</p>
                    <p class="text-default-900">{{ $customer->dob?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Thai PIN</p>
                    <p class="text-default-900 font-mono">{{ $customer->thai_pin ?? 'N/A' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-default-500 text-sm mb-1">Address</p>
                    <p class="text-default-900">{{ $customer->address ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Joined Date</p>
                    <p class="text-default-900">{{ $customer->created_at->format('M d, Y - H:i') }}</p>
                </div>
                <div>
                    <p class="text-default-500 text-sm mb-1">Last Updated</p>
                    <p class="text-default-900">{{ $customer->updated_at->format('M d, Y - H:i') }}</p>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                @can('customer-edit')
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="edit"></i>Edit Customer
                    </a>
                    
                    @if($customer->is_blocked)
                        <form action="{{ route('customers.unblock', $customer->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn bg-success text-white" onclick="return confirm('Unblock this customer account?')">
                                <i class="size-4 me-1" data-lucide="unlock"></i>Unblock Account
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn bg-danger text-white" data-hs-overlay="#blockModal">
                            <i class="size-4 me-1" data-lucide="shield-alert"></i>Block Account
                        </button>
                    @endif
                @endcan
                <a href="{{ route('customers.index') }}" class="btn bg-default-200 text-default-700">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    {{-- Block Customer Modal --}}
    @if(!$customer->is_blocked)
    <div id="blockModal" class="hs-overlay hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1">
        <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
            <div class="card w-full flex flex-col border border-default-200 shadow-2xs rounded-xl pointer-events-auto">
                <div class="card-header">
                    <h3 class="font-semibold text-base text-default-800 flex items-center gap-2">
                        <i class="size-5 text-danger" data-lucide="shield-alert"></i>
                        <span>Block Customer Account</span>
                    </h3>
                    <button type="button" class="size-5 text-default-800" data-hs-overlay="#blockModal">
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.block', $customer->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Reason for Blocking</label>
                            <textarea name="block_reason" class="form-input" rows="4" required placeholder="Why is this account being blocked?"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="btn bg-danger text-white">Confirm Block</button>
                            <button type="button" class="btn bg-default-200" data-hs-overlay="#blockModal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('css')
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
</style>
@endsection

