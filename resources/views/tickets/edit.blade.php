@extends('layouts.vertical', ['title' => 'Edit Lottery Ticket'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Edit Ticket'])

    <div class="max-w-2xl mx-auto card">
        <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data" class="card-body space-y-6">
            @csrf
            @method('PUT')

            @include('tickets.partials.form', ['ticket' => $ticket])

            <div class="flex gap-3">
                <button type="submit" class="btn bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="save"></i>Update
                </button>
                <a href="{{ route('tickets.index') }}" class="btn bg-default-200 text-default-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
