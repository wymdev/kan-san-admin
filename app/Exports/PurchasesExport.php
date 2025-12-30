<?php

namespace App\Exports;

use App\Models\TicketPurchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = TicketPurchase::with(['customer', 'lotteryTicket']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['result_status'])) {
            if ($this->filters['result_status'] === 'won') {
                $query->where('status', 'won');
            } elseif ($this->filters['result_status'] === 'not_won') {
                $query->where('status', 'not_won');
            } elseif ($this->filters['result_status'] === 'unchecked') {
                $query->where('status', 'approved')->whereNull('checked_at');
            }
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('full_name', 'like', "%$search%")
                         ->orWhere('phone_number', 'like', "%$search%");
                  });
            });
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Order Number',
            'Customer Name',
            'Customer Phone',
            'Ticket Name',
            'Ticket Numbers',
            'Quantity',
            'Total Price',
            'Status',
            'Result Status',
            'Purchased At',
            'Approved At',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->id,
            $purchase->order_number,
            $purchase->customer->full_name ?? 'N/A',
            $purchase->customer->phone_number ?? 'N/A',
            $purchase->lotteryTicket->ticket_name ?? 'N/A',
            is_array($purchase->lotteryTicket?->numbers) ? implode('-', $purchase->lotteryTicket->numbers) : 'N/A',
            $purchase->quantity,
            $purchase->total_price,
            ucfirst($purchase->status),
            $purchase->checked_at ? ($purchase->status === 'won' ? 'Won' : 'Not Won') : 'Unchecked',
            $purchase->created_at->format('Y-m-d H:i:s'),
            $purchase->approved_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
