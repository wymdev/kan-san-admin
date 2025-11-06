@extends('layouts.vertical', ['title' => 'Edit Draw Info'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Menu', 'title' => 'Edit Draw Info'])

    <div class="mb-5">
        <a href="{{ route('drawinfos.index') }}" class="btn btn-sm bg-default-200 text-default-600 hover:bg-default-300">
            <i class="size-4 me-1" data-lucide="arrow-left"></i>Back to List
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Edit Draw Info: {{ $draw->period }}</h6>
                </div>
                <form action="{{ route('drawinfos.update', $draw->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body space-y-4">
                        <div>
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Draw Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="draw_date" class="form-input w-full @error('draw_date') border-red-500 @enderror"
                                value="{{ old('draw_date', $draw->draw_date->format('Y-m-d\TH:i')) }}" required>
                            @error('draw_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Result Announce Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="result_announce_date" class="form-input w-full @error('result_announce_date') border-red-500 @enderror"
                                value="{{ old('result_announce_date', $draw->result_announce_date->format('Y-m-d\TH:i')) }}" required>
                            @error('result_announce_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Period <span class="text-red-500">*</span></label>
                            <input type="text" name="period" class="form-input w-full @error('period') border-red-500 @enderror"
                                placeholder="Enter period" value="{{ old('period', $draw->period) }}" required>
                            @error('period')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="inline-block mb-2 text-sm text-default-800 font-medium">Note</label>
                            <textarea name="note" class="form-input w-full @error('note') border-red-500 @enderror"
                                placeholder="Enter note" rows="4">{{ old('note', $draw->note) }}</textarea>
                            @error('note')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_estimated" value="1" id="isEstimated" class="form-checkbox rounded"
                                {{ old('is_estimated', $draw->is_estimated) ? 'checked' : '' }}>
                            <label for="isEstimated" class="text-sm text-default-800 font-medium">Mark as Estimated</label>
                        </div>
                    </div>

                    <div class="card-footer flex gap-2 md:justify-end">
                        <a href="{{ route('drawinfos.index') }}" class="bg-transparent text-default-600 btn border-0 hover:bg-default/10">Cancel</a>
                        <button type="submit" class="btn bg-primary text-white">Update Draw Info</button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('drawinfos.destroy', $draw->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this draw? This action cannot be undone.');">
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
