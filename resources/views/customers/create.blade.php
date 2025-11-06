@extends('layouts.vertical', ['title' => 'Create Customer'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Create Customer'])

    <div class="card max-w-2xl mx-auto">
        <div class="card-header">
            <h6 class="card-title">New Customer</h6>
        </div>
        <form action="{{ route('customers.store') }}" method="POST" class="card-body space-y-4">
            @csrf

            <div class="form-group">
                <label class="form-label" for="phone_number">Phone Number <span class="text-red-600">*</span></label>
                <input 
                    class="form-input @error('phone_number') border-red-500 @enderror" 
                    id="phone_number"
                    name="phone_number"
                    placeholder="+66812345678"
                    type="text"
                    value="{{ old('phone_number') }}"
                />
                @error('phone_number')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input 
                    class="form-input" 
                    id="full_name"
                    name="full_name"
                    type="text"
                    value="{{ old('full_name') }}"
                />
                @error('full_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input 
                    class="form-input" 
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                />
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password <span class="text-red-600">*</span></label>
                <input 
                    class="form-input @error('password') border-red-500 @enderror" 
                    id="password"
                    name="password"
                    type="password"
                />
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password <span class="text-red-600">*</span></label>
                <input 
                    class="form-input" 
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                />
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="save"></i>Create Customer
                </button>
                <a href="{{ route('customers.index') }}" class="btn bg-default-200 text-default-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
