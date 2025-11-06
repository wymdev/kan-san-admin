@extends('layouts.vertical', ['title' => 'Users Management'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Users Management'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Users List</h6>
            @can('user-create')
                <a href="{{ route('users.create') }}" class="btn btn-sm bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="plus"></i>Add User
                </a>
            @endcan
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('users.index') }}" class="w-full flex gap-3">
                    <div class="relative flex-1">
                        <input 
                            class="form-input form-input-sm ps-9 w-full" 
                            placeholder="Search for name, email" 
                            type="text"
                            name="search"
                            value="{{ $search }}"
                        />
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3">
                            <i class="size-3.5 flex items-center text-default-500 fill-default-100"
                            data-lucide="search"></i>
                        </div>
                    </div>
                    <button 
                        type="submit" 
                        class="btn btn-sm bg-primary text-white"
                    >
                        Search
                    </button>
                    @if(!empty($search))
                        <a 
                            href="{{ route('users.index') }}" 
                            class="btn btn-sm bg-default-200 text-default-600 hover:bg-default-300"
                        >
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                            <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                <th class="px-3.5 py-3 text-start" scope="col">No</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Name</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Email</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Roles</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($users as $key => $user)
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">{{ ++$i }}</td>
                                    <td class="flex py-3 px-3.5 items-center gap-3">
                                        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-primary font-semibold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 font-semibold">
                                                <a class="text-default-800" href="{{ route('users.show', $user->id) }}">{{ $user->name }}</a>
                                            </h6>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3.5">{{ $user->email }}</td>
                                    <td class="py-3 px-3.5">
                                        @if(!empty($user->getRoleNames()))
                                            @foreach($user->getRoleNames() as $rolename)
                                                <span class="py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-success/10 text-success rounded">
                                                    {{ $rolename }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <div class="hs-dropdown relative inline-flex">
                                            <button aria-expanded="false" aria-haspopup="menu" aria-label="Dropdown"
                                                    class="hs-dropdown-toggle btn size-7.5 bg-default-200 hover:bg-default-600 text-default-500"
                                                    hs-dropdown-placement="bottom-end" type="button">
                                                <i class="iconify lucide--ellipsis size-4"></i>
                                            </button>
                                            <div class="hs-dropdown-menu" role="menu">
                                                @can('user-list')
                                                    <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                                       href="{{ route('users.show', $user->id) }}">
                                                        <i class="size-3" data-lucide="eye"></i>
                                                        View
                                                    </a>
                                                @endcan
                                                @can('user-edit')
                                                    <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                                       href="{{ route('users.edit', $user->id) }}">
                                                        <i class="size-3" data-lucide="edit"></i>
                                                        Edit
                                                    </a>
                                                @endcan
                                                @can('user-delete')
                                                    <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-danger hover:bg-default-150 rounded w-full text-left"
                                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                                            <i class="size-3" data-lucide="trash-2"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p class="text-default-500 text-sm">Showing <b>{{ $users->count() }}</b> of <b>{{ $users->total() }}</b> Results</p>
                <nav aria-label="Pagination" class="flex items-center gap-2">
                    {{ $users->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection
