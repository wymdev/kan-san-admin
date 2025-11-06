@extends('layouts.vertical', ['title' => 'Add Lottery Ticket'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Add Ticket'])

    <div class="max-w-2xl mx-auto card">
        <form action="{{ route('tickets.store') }}" enctype="multipart/form-data" method="POST" class="card-body space-y-6">
            @csrf

            @include('tickets.partials.form')

            <div class="flex gap-3">
                <button type="submit" class="btn bg-primary text-white">
                    <i class="size-4 me-1" data-lucide="save"></i>Save Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn bg-default-200 text-default-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
