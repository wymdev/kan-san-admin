@extends('layouts.vertical', ['title' => 'Edit Customer'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Edit Customer'])

    {{-- Display All Validation Errors --}}

    <x-ui.card title="Update Customer Profile" class="max-w-3xl mx-auto">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.field name="phone_number" label="Phone Number" required>
                    <x-ui.input required :aria-describedby="$errors->has('phone_number') ? 'phone_number-error' : null"
                        class="form-input "
                        id="phone_number"
                        name="phone_number"
                        type="text"
                        placeholder="+66812345678"
                        value="{{ old('phone_number', $customer->phone_number) }}"
                    />

                </x-ui.field>

                <x-ui.field name="full_name" label="Full Name" required>
                    <x-ui.input required :aria-describedby="$errors->has('full_name') ? 'full_name-error' : null"
                        class="form-input "
                        id="full_name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $customer->full_name) }}"
                    />

                </x-ui.field>

                <x-ui.field name="email" label="Email">
                    <x-ui.input :aria-describedby="$errors->has('email') ? 'email-error' : null"
                        class="form-input "
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $customer->email) }}"
                    />

                </x-ui.field>

                <x-ui.field name="gender" label="Gender">
                    <x-ui.select :aria-describedby="$errors->has('gender') ? 'gender-error' : null" class="form-input" id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="M" {{ old('gender', $customer->gender) == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('gender', $customer->gender) == 'F' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $customer->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field name="dob" label="Date of Birth">
                    <x-ui.input :aria-describedby="$errors->has('dob') ? 'dob-error' : null"
                        class="form-input" 
                        id="dob"
                        name="dob"
                        type="date"
                        value="{{ old('dob', $customer->dob?->format('Y-m-d')) }}"
                     />

                </x-ui.field>

                <x-ui.field name="thai_pin" label="Location PIN">
                    <x-ui.input :aria-describedby="$errors->has('thai_pin') ? 'thai_pin-error' : null"
                        class="form-input "
                        id="thai_pin"
                        name="thai_pin"
                        type="text"
                        placeholder="123456"
                        value="{{ old('thai_pin', $customer->thai_pin) }}"
                    />

                </x-ui.field>
            </div>

            <x-ui.field name="address" label="Address">
                <x-ui.textarea :aria-describedby="$errors->has('address') ? 'address-error' : null"
                    class="form-input" 
                    id="address"
                    name="address"
                    rows="3"
                >{{ old('address', $customer->address) }}</x-ui.textarea>
            </x-ui.field>

            <hr class="my-4">

            <h6 class="font-semibold text-sm mb-3">Change Password <span class="text-default-500 text-xs font-normal">(Optional - only fill if you want to change password)</span></h6>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.field name="password" label="New Password">
                    <x-ui.input :aria-describedby="$errors->has('password') ? 'password-error' : null"
                        class="form-input "
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Leave blank to keep current password"
                        autocomplete="new-password"
                    />

                </x-ui.field>

                <x-ui.field name="password_confirmation" label="Confirm Password">
                    <x-ui.input :aria-describedby="$errors->has('password_confirmation') ? 'password_confirmation-error' : null"
                        class="form-input "
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Must match password field"
                        autocomplete="new-password"
                    />

                </x-ui.field>
            </div>

            <div class="flex gap-3 pt-4">
                <x-ui.button type="submit">
                    <i class="size-4 me-1" data-lucide="save"></i>Update Customer
                </x-ui.button>
                <x-ui.button href="{{ route('customers.index') }}" variant="secondary">
                    Cancel
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
