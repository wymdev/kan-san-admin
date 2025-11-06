@extends('layouts.vertical', ['title' => 'Draw Info Details'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Menu', 'title' => 'Draw Info Details'])

    <div class="mb-5">
        <a href="{{ route('drawinfos.index') }}" class="btn btn-sm bg-default-200 text-default-600 hover:bg-default-300">
            <i class="size-4 me-1" data-lucide="arrow-left"></i>Back to List
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Draw Information</h6>
                </div>
                <div class="card-body space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-default-700 mb-2">Period</label>
                            <p class="text-default-900 font-semibold">{{ $draw->period }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-default-700 mb-2">Status</label>
                            @if($draw->is_estimated)
                                <span class="py-1 px-3 text-xs font-medium bg-warning/10 text-warning rounded">
                                    Estimated
                                </span>
                            @else
                                <span class="py-1 px-3 text-xs font-medium bg-success/10 text-success rounded">
                                    Confirmed
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-default-700 mb-2">Draw Date</label>
                            <p class="text-default-900 font-semibold">{{ $draw->draw_date->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-default-700 mb-2">Announce Date</label>
                            <p class="text-default-900 font-semibold">{{ $draw->result_announce_date->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-default-700 mb-2">Note</label>
                        <p class="text-default-900">{{ $draw->note ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-default-700 mb-2">Created At</label>
                        <p class="text-default-900 text-sm">{{ $draw->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Actions</h6>
                </div>
                <div class="card-body space-y-3">
                    <a href="{{ route('drawinfos.edit', $draw->id) }}" class="btn btn-primary w-full">
                        <i class="size-4 me-2" data-lucide="edit"></i>Edit Draw
                    </a>
                    <form action="{{ route('drawinfos.destroy', $draw->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this draw?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full">
                            <i class="size-4 me-2" data-lucide="trash-2"></i>Delete Draw
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
