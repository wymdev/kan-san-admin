@extends('layouts.vertical', ['title' => 'Draw Info Management'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Menu', 'title' => 'Draw Info Management'])

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
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        </style>

        <script>
            setTimeout(() => {
                const toast = document.getElementById('successToast');
                if (toast) {
                    toast.style.animation = 'slideOut 0.3s ease-out forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        </script>
    @endif

    <div class="flex justify-between gap-3 flex-wrap items-center mb-5">
        <form method="GET" action="{{ route('drawinfos.index') }}" class="relative mb-5 flex items-center">
            <input class="form-input form-input-sm ps-9" placeholder="Search for period, note..." type="text" name="search" value="{{ request('search') }}" />
            <div class="absolute inset-y-0 start-4 flex items-center">
                <i class="size-3.5 flex items-center text-default-500" data-lucide="search"></i>
            </div>
        </form>
        <div class="flex gap-3 items-center">
            <a href="{{ route('drawinfos.create') }}"
                class="btn btn-sm bg-primary text-white">
                <i class="size-4 ms-1" data-lucide="plus"></i>
                Add Draw Info
            </a>
            <button class="btn size-7.5 bg-default-500 text-white hover:bg-default-600" type="button">
                <i class="size-4" data-lucide="sliders-horizontal"></i>
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
        @forelse($draws as $draw)
            <div class="card draw-card" data-draw-period="{{ strtolower($draw->period) }}">
                <div class="card-body">
                    <div class="flex items-center justify-center mx-auto text-lg rounded-full size-16 bg-primary/10">
                        <span class="text-primary font-semibold text-xl">{{ substr($draw->period, 0, 2) }}</span>
                    </div>
                    <div class="mt-4 text-center text-default-500">
                        <h5 class="mb-1 text-base text-default-800 font-semibold">
                            <a href="{{ route('drawinfos.show', $draw->id) }}">{{ $draw->period }}</a>
                        </h5>
                        <p class="mb-3 text-sm text-default-500">
                            {{ $draw->draw_date->format('M d, Y H:i') }}
                        </p>
                        <p class="text-sm text-default-500">
                            Announce: {{ $draw->result_announce_date->format('M d, Y H:i') }}
                        </p>
                        @if($draw->is_estimated)
                            <p class="mt-2 text-xs px-2 py-1 bg-warning/10 text-warning rounded inline-block">
                                Estimated
                            </p>
                        @endif
                    </div>
                    <div class="flex gap-2 mt-5">
                        <a class="btn border-primary text-primary hover:bg-primary hover:text-white flex-grow"
                            href="{{ route('drawinfos.show', $draw->id) }}">
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
                                    href="{{ route('drawinfos.show', $draw->id) }}">
                                    <i class="size-3" data-lucide="eye"></i>
                                    View Details
                                </a>
                                <a class="flex items-center gap-1.5 py-1.5 font-medium px-3 text-default-500 hover:bg-default-150 rounded"
                                    href="{{ route('drawinfos.edit', $draw->id) }}">
                                    <i class="size-3" data-lucide="edit"></i>
                                    Edit
                                </a>
                                <form action="{{ route('drawinfos.destroy', $draw->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this draw?');">
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
                    <p class="text-default-500">No draw info found. <a href="{{ route('drawinfos.create') }}" class="text-primary hover:underline">Create one</a></p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="flex flex-wrap md:justify-between justify-center items-center md:gap-0 gap-4 my-5 text-default-500">
        <p class="text-default-500 text-sm">Showing <b>{{ $draws->count() }}</b> of <b>{{ $draws->total() }}</b> Results</p>
        {{ $draws->appends(['search' => request('search')])->links() }}
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelector('input[name="search"]').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const cards = document.querySelectorAll('.draw-card');
            
            cards.forEach(card => {
                const period = card.getAttribute('data-draw-period');
                if (period.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
@endsection
