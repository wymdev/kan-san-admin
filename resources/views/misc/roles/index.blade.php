@extends('layouts.vertical', ['title' => 'Roles Management'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Menu', 'title' => 'Roles Management'])

    @if ($message = Session::get('success'))
        <div id="successToast" 
            class="fixed top-13 right-4 z-50 mb-4 p-4 bg-success/10 border border-success text-success rounded-md animate-fade-in"
            style="animation: slideIn 0.3s ease-out;">
            <div class="flex items-center justify-between gap-3">
                <span>{{ $message }}</span>
                <button onclick="document.getElementById('successToast').remove()" 
                    class="text-success hover:opacity-70">
                    <i data-lucide="x" class="size-4"></i>
                </button>
            </div>
        </div>

        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        </style>

        <script>
            setTimeout(() => {
                const toast = document.getElementById('successToast');
                if (toast) {
                    toast.style.animation = 'slideOut 0.3s ease-out forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000); // Auto-dismiss after 4 seconds
        </script>
    @endif

    <div class="card mb-5">
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3 justify-between w-full">
                <form method="GET" action="{{ route('roles.index') }}" class="flex gap-3 flex-1">
                    <div class="relative flex-1">
                        <input 
                            class="form-input form-input-sm ps-9 w-full" 
                            placeholder="Search for role name..." 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                        />
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3">
                            <i class="size-3.5" data-lucide="search"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm bg-primary text-white">
                        Search
                    </button>
                    @if(!empty(request('search')))
                        <a href="{{ route('roles.index') }}" class="btn btn-sm bg-default-200 text-default-600">
                            Clear
                        </a>
                    @endif
                </form>
                <div class="flex gap-3">
                    <button aria-controls="addRoleModal" aria-expanded="false" aria-haspopup="dialog"
                        class="btn btn-sm bg-primary text-white" data-hs-overlay="#addRoleModal" type="button">
                        <i class="size-4 me-1" data-lucide="plus"></i>
                        Add Role
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
        @forelse($roles as $role)
            <div class="card role-card" data-role-name="{{ strtolower($role->name) }}">
                <div class="card-body">
                    <div class="flex items-center justify-center mx-auto text-lg rounded-full size-16 bg-primary/10">
                        <span class="text-primary font-semibold text-xl">{{ substr($role->name, 0, 2) }}</span>
                    </div>
                    <div class="mt-4 text-center text-default-500">
                        <h5 class="mb-1 text-base text-default-800 font-semibold">
                            <a href="{{ route('roles.show', $role->id) }}">{{ ucfirst($role->name) }}</a>
                        </h5>
                        <p class="mb-3 text-sm text-default-500">
                            {{ $role->permissions->count() }} Permission{{ $role->permissions->count() !== 1 ? 's' : '' }}
                        </p>
                        <p class="text-sm text-default-500">
                            Created: {{ $role->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="flex gap-2 mt-5">
                        <a class="btn border-primary text-primary hover:bg-primary hover:text-white flex-grow"
                            href="{{ route('roles.show', $role->id) }}">
                            <i class="size-4" data-lucide="eye"></i>
                            <span class="align-middle">View</span>
                        </a>
                        <div class="hs-dropdown relative inline-flex">
                            <button aria-expanded="false" aria-haspopup="menu" aria-label="Dropdown"
                                class="hs-dropdown-toggle btn bg-primary size-9 text-white" hs-dropdown-placement="bottom-end"
                                type="button">
                                <i class="iconify lucide--ellipsis size-4"></i>
                            </button>
                            <div class="hs-dropdown-menu" role="menu">
                                <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                    href="{{ route('roles.show', $role->id) }}">
                                    <i class="size-3" data-lucide="eye"></i>
                                    View Details
                                </a>
                                <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                    href="{{ route('roles.edit', $role->id) }}">
                                    <i class="size-3" data-lucide="edit"></i>
                                    Edit
                                </a>
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded w-full text-left"
                                        style="border:none; background:none;">
                                        <i class="size-3" data-lucide="trash-2"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <p class="text-default-500">No roles found. <a href="{{ route('roles.create') }}" class="text-primary hover:underline">Create one</a></p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="flex flex-wrap md:justify-between justify-center items-center md:gap-0 gap-4 my-5 text-default-500">
        <p class="text-default-500 text-sm">Showing <b>{{ $roles->count() }}</b> of <b>{{ $roles->total() }}</b> Results</p>
        {{ $roles->appends(['search' => request('search')])->links() }}
    </div>

    <!-- Add Role Modal -->
    <div id="addRoleModal"
        class="hs-overlay hidden fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none inset-0"
        role="dialog" tabindex="-1" aria-labelledby="addRoleModal-label">
        <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
            <div class="card w-full flex flex-col border border-default-200 shadow-2xs rounded-xl pointer-events-auto">
                <div class="card-header">
                    <h3 id="addRoleModal-label" class="font-semibold text-base text-default-800 dark:text-white">
                        Create New Role
                    </h3>
                    <button type="button" class="size-5 text-default-800" aria-label="Close"
                        data-hs-overlay="#addRoleModal">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="card-body h-auto" data-simplebar>
                        <div class="mb-4">
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Role Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="form-input @error('name') border-red-500 @enderror"
                                placeholder="Enter role name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Permissions <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                @forelse($permissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permission[]" value="{{ $permission->id }}"
                                            class="form-checkbox" {{ in_array($permission->id, old('permission', [])) ? 'checked' : '' }}>
                                        <span class="text-sm text-default-600">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-default-500">No permissions available</p>
                                @endforelse
                            </div>
                            @error('permission')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer mt-4 flex gap-2 md:justify-end">
                        <button type="button" class="bg-transparent text-danger btn border-0 hover:bg-danger/10"
                            data-hs-overlay="#addRoleModal">Cancel</button>
                        <button type="submit" class="btn bg-primary text-white">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.role-card');
            
            cards.forEach(card => {
                const roleName = card.getAttribute('data-role-name');
                if (roleName.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
@endsection
