@extends('layouts.vertical', ['title' => 'Create Role'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Roles', 'title' => 'Create New Role'])

    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="inline-block mb-2 text-sm text-default-800 font-medium">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-500 @enderror"
                        placeholder="Enter role name (e.g., Admin, Editor, Subscriber)" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="inline-block mb-2 text-sm text-default-800 font-medium">Select Permissions <span class="text-red-500">*</span></label>
                    <div class="border border-default-200 rounded-md p-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @forelse($permission as $perm)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="permission[]" value="{{ $perm->id }}"
                                        class="form-checkbox" {{ in_array($perm->id, old('permission', [])) ? 'checked' : '' }}>
                                    <span class="text-sm text-default-600">{{ ucfirst(str_replace('-', ' ', $perm->name)) }}</span>
                                </label>
                            @empty
                                <p class="text-default-500 col-span-full">No permissions available.</p>
                            @endforelse
                        </div>
                    </div>
                    @error('permission')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-2 justify-end mt-6">
                    <a href="{{ route('roles.index') }}" class="btn bg-default-200 text-default-800 hover:bg-default-300">Cancel</a>
                    <button type="submit" class="btn bg-primary text-white">Create Role</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
@endsection
