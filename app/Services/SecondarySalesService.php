<?php

namespace App\Services;

use App\Models\SecondarySalesTransaction;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecondarySalesService
{
    /**
     * Generate a unique transaction number.
     * Format: YYMMXXXXX
     */
    public function generateTransactionNumber()
    {
        $prefix = date('ym');

        $latestTransaction = DB::table('secondary_sales_transactions')
            ->where('transaction_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(transaction_number) DESC')
            ->orderBy('transaction_number', 'desc')
            ->value('transaction_number');

        if (!$latestTransaction) {
            return $prefix . '00001';
        }

        $numericPart = (int) substr($latestTransaction, strlen($prefix));
        $nextSequence = $numericPart + 1;

        return $prefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Find or create a customer based on input data.
     */
    public function findOrCreateCustomer($data, $createIfNotFound = false)
    {
        // 1. Direct ID selection
        if (!empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }

        // 2. Search by phone
        $customer = null;
        if (!empty($data['customer_phone'])) {
            $customer = Customer::where('phone_number', $data['customer_phone'])->first();
        }

        // 3. Create if not found and requested
        if (!$customer && $createIfNotFound) {
            $customer = Customer::create([
                'full_name' => $data['customer_name'] ?? 'Customer ' . substr($data['customer_phone'] ?? uniqid(), -6),
                'phone_number' => $data['customer_phone'] ?? null,
                'password' => bcrypt('123456'),
            ]);
        }

        return $customer;
    }

    /**
     * Generate CSV export stream for transactions.
     */
    public function exportTransactions($transactions)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="secondary_transactions_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Transaction Number',
                'Ticket Number',
                'Customer Name',
                'Customer Phone',
                'Purchased At',
                'Amount',
                'Status',
                'Prize Won',
                'Draw Date',
                'Paid',
                'Payment Method',
                'Payment Date',
                'Notes'
            ]);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->transaction_number,
                    $t->secondaryTicket?->ticket_number ?? '-',
                    $t->customer_display_name,
                    $t->customer_display_phone,
                    $t->purchased_at?->format('Y-m-d H:i'),
                    $t->amount,
                    $t->status,
                    $t->prize_won ?? '-',
                    $t->drawResult?->date_en ?? '-',
                    $t->is_paid ? 'Yes' : 'No',
                    $t->payment_method ?? '-',
                    $t->payment_date?->format('Y-m-d H:i') ?? '-',
                    $t->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
