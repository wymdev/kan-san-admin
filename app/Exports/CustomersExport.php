<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Customer::query();
        
        if (!empty($this->search)) {
            $query->where('phone_number', 'like', '%' . $this->search . '%')
                  ->orWhere('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }
        
        return $query->orderBy('id', 'DESC')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Phone Number',
            'Full Name',
            'Email',
            'Gender',
            'Date of Birth',
            'Thai PIN',
            'Address',
            'Status',
            'Block Reason',
            'Created Date',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->phone_number,
            $customer->full_name,
            $customer->email,
            $customer->gender,
            $customer->dob?->format('Y-m-d'),
            $customer->thai_pin,
            $customer->address,
            $customer->is_blocked ? 'Blocked' : 'Active',
            $customer->block_reason,
            $customer->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
