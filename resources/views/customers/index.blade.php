@extends('layouts.vertical', ['title' => 'Customers Management'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Customers Management'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Customers List</h6>
            <div class="flex gap-2">
                <a href="{{ route('customers.export', request()->query()) }}" class="btn btn-sm bg-success text-white">
                    <i class="size-4 me-1" data-lucide="download"></i>Export Excel
                </a>
                @can('customer-create')
                    <a href="{{ route('customers.create') }}" class="btn btn-sm bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="plus"></i>Add Customer
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3">
                <form method="GET" action="{{ route('customers.index') }}" class="w-full flex gap-3">
                    <div class="relative flex-1">
                        <input 
                            class="form-input form-input-sm ps-9 w-full" 
                            placeholder="Search for phone, name, email" 
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
                            href="{{ route('customers.index') }}" 
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
                                <th class="px-3.5 py-3 text-start" scope="col">Phone</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Name</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Email</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Status</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Joined</th>
                                <th class="px-3.5 py-3 text-start" scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($customers as $key => $customer)
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap {{ $customer->is_blocked ? 'bg-danger/5' : '' }}">
                                    <td class="px-3.5 py-3">{{ ++$i }}</td>
                                    <td class="px-3.5 py-3 font-mono">{{ $customer->phone_number }}</td>
                                    <td class="flex py-3 px-3.5 items-center gap-3">
                                        <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-primary font-semibold">
                                                {{ strtoupper(substr($customer->full_name ?? 'C', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 font-semibold">
                                                <a class="text-default-800" href="{{ route('customers.show', $customer->id) }}">
                                                    {{ $customer->full_name ?? 'N/A' }}
                                                </a>
                                            </h6>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3.5">{{ $customer->email ?? 'N/A' }}</td>
                                    <td class="py-3 px-3.5">
                                        @if($customer->is_blocked)
                                            <span class="inline-flex px-2 py-1 bg-danger text-white rounded text-xs font-bold">
                                                <i class="size-3 mr-1" data-lucide="shield-alert"></i> Blocked
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 bg-success/10 text-success rounded text-xs">
                                                <i class="size-3 mr-1" data-lucide="check-circle"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-xs">{{ $customer->created_at->format('M d, Y') }}</td>
                                    <td class="px-3.5 py-3">
                                        <div class="hs-dropdown relative inline-flex">
                                            <button aria-expanded="false" aria-haspopup="menu" aria-label="Dropdown"
                                                    class="hs-dropdown-toggle btn size-7.5 bg-default-200 hover:bg-default-600 text-default-500"
                                                    hs-dropdown-placement="bottom-end" type="button">
                                                <i class="iconify lucide--ellipsis size-4"></i>
                                            </button>
                                            <div class="hs-dropdown-menu" role="menu">
                                                @can('customer-list')
                                                    <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                                       href="{{ route('customers.show', $customer->id) }}">
                                                        <i class="size-3" data-lucide="eye"></i>
                                                        View
                                                    </a>
                                                @endcan
                                                @can('customer-edit')
                                                    <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                                       href="{{ route('customers.edit', $customer->id) }}">
                                                        <i class="size-3" data-lucide="edit"></i>
                                                        Edit
                                                    </a>
                                                @endcan
                                                @can('customer-delete')
                                                    <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-danger hover:bg-default-150 rounded w-full text-left"
                                                                onclick="return confirm('Are you sure you want to delete this customer?')">
                                                            <i class="size-3" data-lucide="trash-2"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-default-800 font-normal text-sm">
                                    <td colspan="6" class="px-3.5 py-8 text-center text-default-500">
                                        No customers found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p class="text-default-500 text-sm">Showing <b>{{ $customers->count() }}</b> of <b>{{ $customers->total() }}</b> Results</p>
                <nav aria-label="Pagination" class="flex items-center gap-2">
                    {{ $customers->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection
