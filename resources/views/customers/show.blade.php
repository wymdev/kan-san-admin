@extends('layouts.vertical', ['title' => 'Customer Details'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Customer Details'])

    <div class="card max-w-2xl mx-auto">
        <div class="card-header">
            <h6 class="card-title">Customer Information</h6>
        </div>
        <div class="card-body space-y-6">
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
                @endcan
                <a href="{{ route('customers.index') }}" class="btn bg-default-200 text-default-700">
                    Back to List
                </a>
            </div>
        </form>
    </div>
@endsection
