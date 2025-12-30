<?php

namespace App\Exports;

use App\Models\SecondaryLotteryTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SecondaryTicketsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SecondaryLotteryTicket::with(['transactions']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bar_code', 'like', "%{$search}%")
                  ->orWhere('source_seller', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['withdraw_date'])) {
            $query->whereDate('withdraw_date', $this->filters['withdraw_date']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['has_transactions'])) {
            if ($this->filters['has_transactions'] === 'yes') {
                $query->has('transactions');
            } elseif ($this->filters['has_transactions'] === 'no') {
                $query->doesntHave('transactions');
            }
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Ticket Number',
            'Batch Number',
            'Draw Date',
            'Price',
            'Source Seller',
            'Status',
            'Transactions Count',
            'Created Date',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->ticket_number,
            $ticket->batch_number,
            $ticket->withdraw_date?->format('Y-m-d'),
            $ticket->price,
            $ticket->source_seller,
            $ticket->transactions->count() > 0 ? 'Sold' : 'Unsold',
            $ticket->transactions->count(),
            $ticket->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
