@extends('layouts.vertical', ['title' => 'User Details'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'User Details'])

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">User Information</h6>
            <a href="{{ route('users.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                <i class="size-4 me-1" data-lucide="arrow-left"></i>Back
            </a>
        </div>
        <div class="card-body">
            <div class="flex items-center gap-4 mb-8 pb-8">
                <div class="size-20 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="text-primary font-semibold text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h4 class="text-xl font-semibold text-default-800 mb-1">{{ $user->name }}</h4>
                    <p class="text-default-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="w-full flex items-center gap-4 mb-8 pb-8 border-b border-default-200"></div>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-default-500 mb-1">Name</label>
                        <p class="text-default-800 font-medium">{{ $user->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-default-500 mb-1">Email</label>
                        <p class="text-default-800 font-medium">{{ $user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-default-500 mb-1">Created At</label>
                        <p class="text-default-800 font-medium">{{ $user->created_at->format('d M, Y') }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-default-500 mb-2">Roles</label>
                    <div class="flex flex-wrap gap-2">
                        @if(!empty($user->getRoleNames()))
                            @foreach($user->getRoleNames() as $rolename)
                                <span class="py-1.5 px-3 inline-flex items-center gap-x-1 text-sm font-medium bg-success/10 text-success rounded">
                                    <i class="size-3.5" data-lucide="shield-check"></i>
                                    {{ $rolename }}
                                </span>
                            @endforeach
                        @else
                            <span class="py-1.5 px-3 inline-flex items-center gap-x-1 text-sm font-medium bg-default-100 text-default-600 rounded">
                                No roles assigned
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-8 border-t border-default-200">
                @can('user-edit')
                    <a href="{{ route('users.edit', $user->id) }}" class="btn bg-primary text-white hover:bg-primary/90">
                        <i class="size-4 me-1" data-lucide="edit"></i>
                        Edit User
                    </a>
                @endcan
                @can('user-delete')
                    <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-danger text-white hover:bg-danger/90"
                                onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="size-4 me-1" data-lucide="trash-2"></i>
                            Delete User
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@endsection
