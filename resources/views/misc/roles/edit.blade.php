@extends('layouts.vertical', ['title' => 'Edit Role'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Roles', 'title' => 'Edit Role'])

    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="inline-block mb-2 text-sm text-default-800 font-medium">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-500 @enderror"
                        placeholder="Enter role name" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="inline-block mb-2 text-sm text-default-800 font-medium">Permissions <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($permission as $perm)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permission[]" value="{{ $perm->id }}"
                                    class="form-checkbox" {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                <span class="text-sm text-default-600">{{ ucfirst(str_replace('-', ' ', $perm->name)) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permission')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-2 justify-end mt-6">
                    <a href="{{ route('roles.index') }}" class="btn bg-default-200 text-default-800 hover:bg-default-300">Cancel</a>
                    <button type="submit" class="btn bg-primary text-white">Update Role</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
@endsection
