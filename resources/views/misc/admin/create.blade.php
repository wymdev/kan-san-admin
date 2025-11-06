@extends('layouts.vertical', ['title' => 'Create User'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Create New User'])

    @if (count($errors) > 0)
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">User Information</h6>
            <a href="{{ route('users.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                <i class="size-4 me-1" data-lucide="arrow-left"></i>Back
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="grid lg:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-default-700 mb-2">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" placeholder="Enter user name" 
                               class="form-input @error('name') border-danger @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-default-700 mb-2">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" placeholder="Enter email address" 
                               class="form-input @error('email') border-danger @enderror" value="{{ old('email') }}" required>
                        @error('email')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-default-700 mb-2">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" placeholder="Enter password" 
                               class="form-input @error('password') border-danger @enderror" required>
                        @error('password')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="confirm-password" class="block text-sm font-medium text-default-700 mb-2">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm password" 
                               class="form-input" required>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-default-700 mb-2">Role <span class="text-danger">*</span></label>
                        <div class="grid md:grid-cols-3 gap-3">
                            @foreach($roles as $value => $label)
                                <div class="flex items-center">
                                    <input type="checkbox" name="roles[]" value="{{ $value }}" 
                                           id="role-{{ $value }}" class="form-checkbox text-primary">
                                    <label for="role-{{ $value }}" class="ms-2 text-sm text-default-700">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('users.index') }}" class="btn bg-default-200 text-default-600 hover:bg-default-300">
                        Cancel
                    </a>
                    <button type="submit" class="btn bg-primary text-white hover:bg-primary/90">
                        <i class="size-4 me-1" data-lucide="save"></i>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
