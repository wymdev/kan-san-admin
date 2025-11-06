@extends('layouts.vertical', ['title' => 'Ticket Details'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Ticket Details'])

    <div class="max-w-2xl mx-auto card">
        <div class="card-header">
            <h6 class="card-title">Ticket Barcode: {{ $ticket->bar_code }}</h6>
        </div>
        <div class="card-body grid grid-cols-2 gap-4">
            <div><strong>Name:</strong> {{ $ticket->ticket_name }}</div>
            <div><strong>Type:</strong> {{ ucfirst($ticket->ticket_type) }}</div>
            <div><strong>Signature:</strong> {{ $ticket->signature }}</div>
            <div><strong>Numbers:</strong> {{ is_array($ticket->numbers) ? implode(', ', $ticket->numbers) : $ticket->numbers }}</div>
            <div><strong>Period:</strong> {{ $ticket->period }}</div>
            <div><strong>Big Number:</strong> {{ $ticket->big_num }}</div>
            <div><strong>Set No:</strong> {{ $ticket->set_no }}</div>
            <div><strong>Withdraw Date:</strong> {{ $ticket->withdraw_date ? $ticket->withdraw_date->format('d M Y') : '-' }}</div>
            <div><strong>Price:</strong> {{ number_format($ticket->price,2) }}</div>
            <div><strong>Left Icon:</strong> {{ $ticket->left_icon }}</div>
            <div class="col-span-2"><strong>Description:</strong><br>{{ $ticket->description }}</div>
        </div>
        <div class="card-footer flex gap-3 pt-4 mb-4 mt-8">
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn bg-primary text-white">
                <i class="size-4 me-1" data-lucide="edit"></i>Edit
            </a>
            <a href="{{ route('tickets.index') }}" class="btn bg-default-200 text-default-700">
                Back to List
            </a>
        </div>
    </div>
@endsection
