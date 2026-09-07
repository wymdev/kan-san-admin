@extends('layouts.vertical', ['title' => 'Create Customer'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Create Customer'])

    <x-ui.card title="New Customer" class="max-w-2xl mx-auto">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-ui.field name="phone_number" label="Phone Number" required>
                <x-ui.input required :aria-describedby="$errors->has('phone_number') ? 'phone_number-error' : null"
                    class="form-input "
                    id="phone_number"
                    name="phone_number"
                    placeholder="+66812345678"
                    type="text"
                    value="{{ old('phone_number') }}"
                />

            </x-ui.field>

            <x-ui.field name="full_name" label="Full Name" required>
                <x-ui.input required :aria-describedby="$errors->has('full_name') ? 'full_name-error' : null"
                    class="form-input" 
                    id="full_name"
                    name="full_name"
                    type="text"
                    value="{{ old('full_name') }}"
                 />

            </x-ui.field>

            <x-ui.field name="email" label="Email">
                <x-ui.input :aria-describedby="$errors->has('email') ? 'email-error' : null"
                    class="form-input" 
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                 />

            </x-ui.field>

            <x-ui.field name="password" label="Password" required>
                <x-ui.input required :aria-describedby="$errors->has('password') ? 'password-error' : null"
                    class="form-input "
                    id="password"
                    name="password"
                    type="password"
                />

            </x-ui.field>

            <x-ui.field name="password_confirmation" label="Confirm Password" required>
                <x-ui.input required :aria-describedby="$errors->has('password_confirmation') ? 'password_confirmation-error' : null"
                    class="form-input" 
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                 />
            </x-ui.field>

            <div class="flex gap-3 pt-4">
                <x-ui.button type="submit">
                    <i class="size-4 me-1" data-lucide="save"></i>Create Customer
                </x-ui.button>
                <x-ui.button href="{{ route('customers.index') }}" variant="secondary">
                    Cancel
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
