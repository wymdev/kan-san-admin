<?php

namespace App\Exports;

use App\Models\LotteryTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = LotteryTicket::query();

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('bar_code', 'like', "%$search%")
                    ->orWhere('ticket_name', 'like', "%$search%")
                    ->orWhere('signature', 'like', "%$search%")
                    ->orWhere('period', 'like', "%$search%")
                    ->orWhere('big_num', 'like', "%$search%");
            });
        }
        
        if (!empty($this->filters['ticket_type'])) {
            $query->where('ticket_type', $this->filters['ticket_type']);
        }
        
        if (!empty($this->filters['withdraw_date'])) {
            $query->whereDate('withdraw_date', $this->filters['withdraw_date']);
        }

        return $query->orderBy('withdraw_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Bar Code',
            'Ticket Name',
            'Type',
            'Signature',
            'Numbers',
            'Period',
            'Big Number',
            'Set No',
            'Withdraw Date',
            'Price',
            'Created Date',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->bar_code,
            $ticket->ticket_name,
            ucfirst($ticket->ticket_type),
            $ticket->signature,
            is_array($ticket->numbers) ? implode(' ', $ticket->numbers) : $ticket->numbers,
            $ticket->period,
            $ticket->big_num,
            $ticket->set_no,
            $ticket->withdraw_date?->format('Y-m-d'),
            $ticket->price,
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
