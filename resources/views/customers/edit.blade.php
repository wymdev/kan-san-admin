@extends('layouts.vertical', ['title' => 'Edit Customer'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Edit Customer'])

    {{-- Display All Validation Errors --}}
    @if ($errors->any())
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <h6 class="font-semibold mb-2">Validation Errors:</h6>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card max-w-3xl mx-auto">
        <div class="card-header">
            <h6 class="card-title">Update Customer Profile</h6>
        </div>
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="card-body space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label" for="phone_number">Phone Number <span class="text-red-600">*</span></label>
                    <input 
                        class="form-input @error('phone_number') border-red-500 @enderror" 
                        id="phone_number"
                        name="phone_number"
                        type="text"
                        placeholder="+66812345678"
                        value="{{ old('phone_number', $customer->phone_number) }}"
                    />
                    @error('phone_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name <span class="text-red-600">*</span></label>
                    <input 
                        class="form-input @error('full_name') border-red-500 @enderror" 
                        id="full_name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $customer->full_name) }}"
                    />
                    @error('full_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input 
                        class="form-input @error('email') border-red-500 @enderror" 
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $customer->email) }}"
                    />
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="gender">Gender</label>
                    <select class="form-input" id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="M" {{ old('gender', $customer->gender) == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('gender', $customer->gender) == 'F' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $customer->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="dob">Date of Birth</label>
                    <input 
                        class="form-input" 
                        id="dob"
                        name="dob"
                        type="date"
                        value="{{ old('dob', $customer->dob?->format('Y-m-d')) }}"
                    />
                    @error('dob')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="thai_pin">Location PIN</label>
                    <input 
                        class="form-input @error('thai_pin') border-red-500 @enderror" 
                        id="thai_pin"
                        name="thai_pin"
                        type="text"
                        placeholder="123456"
                        value="{{ old('thai_pin', $customer->thai_pin) }}"
                    />
                    @error('thai_pin')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea 
                    class="form-input" 
                    id="address"
                    name="address"
                    rows="3"
                >{{ old('address', $customer->address) }}</textarea>
            </div>

            <hr class="my-4">

            <h6 class="font-semibold text-sm mb-3">Change Password <span class="text-default-500 text-xs font-normal">(Optional - only fill if you want to change password)</span></h6>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input 
                        class="form-input @error('password') border-red-500 @enderror" 
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Leave blank to keep current password"
                        autocomplete="new-password"
                    />
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input 
                        class="form-input @error('password_confirmation') border-red-500 @enderror" 
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Must match password field"
                        autocomplete="new-password"
                    />
                    @error('password_confirmation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="save"></i>Update Customer
                </button>
                <a href="{{ route('customers.index') }}" class="btn bg-default-200 text-default-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
