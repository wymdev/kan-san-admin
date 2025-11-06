@extends('layouts.vertical', ['title' => 'View Role'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Roles', 'title' => 'View Role'])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Role Details Card -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-center mx-auto text-lg rounded-full size-20 bg-primary/10">
                        <span class="text-primary font-semibold text-2xl">{{ substr($role->name, 0, 2) }}</span>
                    </div>
                    <div class="mt-4 text-center">
                        <h3 class="text-xl font-semibold text-default-800">{{ ucfirst($role->name) }}</h3>
                        <p class="text-sm text-default-500 mt-2">Created: {{ $role->created_at->format('M d, Y') }}</p>
                        <p class="text-sm text-default-500">Updated: {{ $role->updated_at->format('M d, Y') }}</p>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn bg-primary text-white flex-grow">
                            <i class="size-4" data-lucide="edit"></i>
                            Edit Role
                        </a>
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn bg-danger text-white">
                                <i class="size-4" data-lucide="trash-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-base font-semibold text-default-800">Assigned Permissions</h5>
                </div>
                <div class="card-body">
                    @if($rolePermissions->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($rolePermissions as $permission)
                                <div class="flex items-center gap-2 p-3 bg-default-50 rounded-md">
                                    <i class="size-4 text-success" data-lucide="check-circle"></i>
                                    <span class="text-sm text-default-600">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-default-500 text-center py-8">No permissions assigned to this role.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('roles.index') }}" class="btn bg-default-200 text-default-800 hover:bg-default-300">
            <i class="size-4" data-lucide="chevron-left"></i>
            Back to Roles
        </a>
    </div>

@endsection

@section('scripts')
@endsection
