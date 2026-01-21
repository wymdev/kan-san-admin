<?php

namespace App\Http\Controllers;

use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use App\Models\Customer;
use App\Services\LotteryResultCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecondarySalesController extends Controller
{
    protected $checkerService;

    public function __construct(LotteryResultCheckerService $checkerService)
    {
        $this->checkerService = $checkerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->leftJoin('secondary_lottery_tickets', 'secondary_sales_transactions.secondary_ticket_id', '=', 'secondary_lottery_tickets.id')
            ->leftJoin('draw_results', 'secondary_sales_transactions.draw_result_id', '=', 'draw_results.id')
            ->select(
                'secondary_sales_transactions.*',
                'secondary_lottery_tickets.signature',
                'secondary_lottery_tickets.withdraw_date',
                'draw_results.date_en as draw_date'
            );

        // Apply filters
        if ($status = $request->input('status')) {
            $query->where('secondary_sales_transactions.status', $status);
        }
        if ($request->filled('is_paid')) {
            $query->where('secondary_sales_transactions.is_paid', $request->is_paid === 'yes');
        }
        if ($request->filled('date_from')) {
            $query->where('secondary_sales_transactions.purchased_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('secondary_sales_transactions.purchased_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('secondary_sales_transactions.transaction_number', 'like', "%{$search}%")
                    ->orWhere('secondary_lottery_tickets.signature', 'like', "%{$search}%")
                    ->orWhere('secondary_sales_transactions.customer_name', 'like', "%{$search}%")
                    ->orWhere('secondary_sales_transactions.customer_phone', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('secondary_sales_transactions.purchased_at')->paginate(25);

        return view('secondary-sales.transactions.index', compact('transactions'))
            ->with('filters', $request->only(['status', 'is_paid', 'payment_method', 'date_from', 'date_to', 'search']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('full_name')->get();

        // Get tickets that haven't been sold yet (no transactions)
        $tickets = SecondaryLotteryTicket::doesntHave('transactions')
            ->orderBy('withdraw_date')
            ->get();

        return view('secondary-sales.transactions.create', compact('customers', 'tickets'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secondary_ticket_id' => 'required|uuid|exists:secondary_lottery_tickets,id',
            'amount_thb' => 'nullable|numeric|min:0',
            'amount_mmk' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'is_paid' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->amount_thb && !$request->amount_mmk) {
                $validator->errors()->add('amount_thb', 'Either THB or MMK amount is required');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $ticket = SecondaryLotteryTicket::find($request->secondary_ticket_id);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            $customerId = null;
            if ($request->filled('customer_name')) {
                $customer = Customer::firstOrCreate(
                    ['full_name' => $request->customer_name],
                    ['phone_number' => $request->customer_phone ?? null]
                );
                $customerId = $customer->id;
            }

            $transaction = SecondarySalesTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'secondary_ticket_id' => $request->secondary_ticket_id,
                'customer_id' => $customerId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'purchased_at' => $request->purchased_at,
                'amount_thb' => $request->amount_thb ?: 0,
                'amount_mmk' => $request->amount_mmk ?: 0,
                'is_paid' => $request->boolean('is_paid', false),
                'payment_method' => $request->payment_method,
                'payment_date' => $request->is_paid ? now() : null,
                'notes' => $request->notes,
                'sale_type' => 'own',
                'created_by' => auth()->id(),
                'public_token' => Str::random(32),
                'batch_token' => Str::random(32),
            ]);

            $message = $this->generateSuccessMessage($transaction);
            DB::commit();

            return redirect()->route('secondary-transactions.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Secondary Transaction Store Error: ' . $e->getMessage());
            return back()
                ->with('error', 'Transaction creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SecondarySalesTransaction $secondaryTransaction)
    {
        $secondaryTransaction->load(['secondaryTicket', 'customer', 'drawResult', 'createdBy']);

        return view('secondary-sales.transactions.show', compact('secondaryTransaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SecondarySalesTransaction $secondaryTransaction)
    {
        $secondaryTransaction->load(['secondaryTicket', 'customer']);
        $tickets = SecondaryLotteryTicket::latest()->take(50)->get();

        return view('secondary-sales.transactions.edit', compact('secondaryTransaction', 'tickets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SecondarySalesTransaction $secondaryTransaction)
    {
        $validator = \Validator::make($request->all(), [
            'secondary_ticket_id' => 'required|uuid|exists:secondary_lottery_tickets,id',
            'amount_thb' => 'nullable|numeric|min:0',
            'amount_mmk' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'is_paid' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->amount_thb && !$request->amount_mmk) {
                $validator->errors()->add('amount_thb', 'Either THB or MMK amount is required');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle customer creation/update
            $customerId = null;
            if ($request->filled('customer_name')) {
                $customer = Customer::firstOrCreate(
                    ['full_name' => $request->customer_name],
                    ['phone_number' => $request->customer_phone ?? null]
                );
                $customerId = $customer->id;
            }

            $secondaryTransaction->update([
                'secondary_ticket_id' => $request->secondary_ticket_id,
                'customer_id' => $customerId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'purchased_at' => $request->purchased_at,
                'amount_thb' => $request->amount_thb ?: 0,
                'amount_mmk' => $request->amount_mmk ?: 0,
                'is_paid' => $request->boolean('is_paid', false),
                'payment_method' => $request->payment_method,
                'payment_date' => $request->is_paid ? now() : null,
                'notes' => $request->notes,
            ]);

            $message = 'Transaction updated successfully!';

            // Add batch link if paid
            if ($request->is_paid && $secondaryTransaction->batch_token) {
                $batchLink = route('public.customer-batch', ['token' => $secondaryTransaction->batch_token]);
                $message .= ' <br><small>📱 Batch Link: <a href="' . $batchLink . '" target="_blank" class="text-primary">' . $batchLink . '</a></small>';
            } elseif ($request->is_paid && $secondaryTransaction->public_token) {
                $message .= ' <br><small>📱 Public Link: <a href="' . $secondaryTransaction->public_result_url . '" target="_blank" class="text-primary">' . $secondaryTransaction->public_result_url . '</a></small>';
            }

            DB::commit();

            return redirect()->route('secondary-transactions.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Secondary Transaction Update Error: ' . $e->getMessage());
            return back()
                ->with('error', 'Transaction update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SecondarySalesTransaction $secondaryTransaction)
    {
        $secondaryTransaction->delete();

        return redirect()->route('secondary-transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Mark a transaction as paid
     */
    public function markPaid(Request $request, SecondarySalesTransaction $secondaryTransaction)
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
        ]);

        $secondaryTransaction->update([
            'is_paid' => true,
            'payment_method' => $request->payment_method,
            'payment_date' => now(),
        ]);

        return back()->with('success', 'Transaction marked as paid!');
    }

    /**
     * Check results page
     */
    public function checkResultsPage(Request $request)
    {
        try {
            $stats = $this->checkerService->getTransactionStatistics();
            $statusGroups = $this->checkerService->getTransactionsByDrawStatus();

            // Paginate ready to check
            $readyToCheckCollection = $statusGroups['ready_to_check'];
            $perPage = 20;
            $currentPage = $request->get('ready_page', 1);
            $readyToCheck = new \Illuminate\Pagination\LengthAwarePaginator(
                $readyToCheckCollection->forPage($currentPage, $perPage),
                $readyToCheckCollection->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'ready_page']
            );

            // Paginate and search previously checked
            $previouslyCheckedQuery = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
                ->whereIn('status', ['won', 'not_won'])
                ->whereNotNull('checked_at')
                ->whereNotNull('draw_result_id');

            // Apply search
            if ($search = $request->get('search')) {
                $previouslyCheckedQuery->where(function ($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhereHas('secondaryTicket', function ($sq) use ($search) {
                            $sq->where('signature', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($statusFilter = $request->get('status_filter')) {
                $previouslyCheckedQuery->where('status', $statusFilter);
            }

            $previouslyChecked = $previouslyCheckedQuery
                ->latest('checked_at')
                ->paginate(20, ['*'], 'checked_page');

            $recentWinners = SecondarySalesTransaction::with(['customer', 'secondaryTicket', 'drawResult'])
                ->where('status', 'won')
                ->latest('checked_at')
                ->take(10)
                ->get();

            return view('secondary-sales.transactions.check-results', compact(
                'stats',
                'readyToCheck',
                'statusGroups',
                'recentWinners',
                'previouslyChecked'
            ));

        } catch (\Exception $e) {
            return redirect()->route('secondary-transactions.index')
                ->with('error', '❌ Error loading check results page: ' . $e->getMessage());
        }
    }

    /**
     * Check all pending transactions against lottery results
     */
    public function checkResults()
    {
        try {
            $result = $this->checkerService->checkAllPendingTransactions();

            $flashType = $result['type'] ?? 'info';

            return redirect()->back()->with($flashType, $result['message']);

        } catch (\Exception $e) {
            \Log::error('Secondary Check Results Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ System Error: ' . $e->getMessage());
        }
    }

    /**
     * Recheck all previously checked transactions
     */
    public function recheckAll()
    {
        try {
            $result = $this->checkerService->recheckAllTransactions();

            $flashType = $result['type'] ?? 'info';

            return redirect()->back()->with($flashType, $result['message']);

        } catch (\Exception $e) {
            \Log::error('Secondary Recheck All Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ System Error: ' . $e->getMessage());
        }
    }

    /**
     * Recheck selected transactions
     */
    public function recheckSelected(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:secondary_sales_transactions,id',
        ]);

        try {
            $result = $this->checkerService->recheckTransactions($request->input('transaction_ids'));

            $flashType = $result['type'] ?? 'info';

            return redirect()->back()->with($flashType, $result['message']);

        } catch (\Exception $e) {
            \Log::error('Secondary Recheck Selected Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ System Error: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        $query = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult']);

        // Apply same filters as index
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->is_paid === 'yes');
        }
        if ($request->filled('date_from')) {
            $query->where('purchased_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('purchased_at', '<=', $request->date_to . ' 23:59:59');
        }

        $transactions = $query->latest('purchased_at')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="secondary_transactions_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

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
                'Notes',
            ]);

            // Data rows
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

    /**
     * Search customers (AJAX endpoint)
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->input('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $customers = Customer::where('full_name', 'like', "%{$search}%")
            ->orWhere('phone_number', 'like', "%{$search}%")
            ->take(10)
            ->get(['id', 'full_name', 'phone_number']);

        return response()->json($customers);
    }

    private function generateTransactionNumber()
    {
        $prefix = date('ym');
        $counter = DB::table('secondary_sales_transactions')->where('transaction_number', 'like', $prefix . '%')->count();
        return $prefix . str_pad($counter + 1, 3, '0');
    }

    private function generateSuccessMessage(SecondarySalesTransaction $transaction)
    {
        $saleType = $transaction->sale_type;
        $saleLabel = ucfirst($saleType);
        $customerName = $transaction->customer_display_name ?: 'Walk-in Customer';
        $ticketNumber = $transaction->secondaryTicket->ticket_number;

        if ($transaction->is_paid) {
            $paymentMethod = $transaction->payment_method ?? 'N/A';
            $saleStatus = '✅ Transaction completed successfully!';
            $additionalInfo = "<br>Customer: <strong>{$customerName}</strong><br>Ticket: <strong>{$ticketNumber}</strong><br>Payment: {$paymentMethod}";
        } else {
            $saleStatus = '⚠️ Transaction recorded but payment pending!';
            $additionalInfo = "<br>Customer: <strong>{$customerName}</strong><br>Ticket: <strong>{$ticketNumber}</strong><br><span style=\"color: orange;\">💰 Payment pending</span>";
        }

        $message = "🎟 {$saleLabel} Sale {$saleStatus}{$additionalInfo}";

        if ($transaction->public_token) {
            $publicUrl = route('public.customer-batch', ['token' => $transaction->public_token]);
            $message .= ' <br><small>📱 Public Result URL: <a href="' . $publicUrl . '" target="_blank" class="text-primary">' . $publicUrl . '</a></small>';
        }
        if ($transaction->batch_token) {
            $batchUrl = route('public.customer-batch', ['token' => $transaction->batch_token]);
            $message .= ' <br><small>📱 Batch URL: <a href="' . $batchUrl . '" target="_blank" class="text-primary">' . $batchUrl . '</a></small>';
        }

        return $message;
    }
}