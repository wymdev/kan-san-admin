<?php

namespace App\Http\Controllers;

use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use App\Models\Customer;
use App\Services\SecondaryResultCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecondarySalesController extends Controller
{
    protected $checkerService;

    public function __construct(SecondaryResultCheckerService $checkerService)
    {
        $this->checkerService = $checkerService;
    }

    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $query = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult', 'createdBy']);

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Payment status filter
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->is_paid === 'yes');
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Customer search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('purchased_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('purchased_at', '<=', $request->date_to . ' 23:59:59');
        }

        $transactions = $query->latest('purchased_at')->paginate(20)->appends($request->query());

        // Get statistics
        $stats = $this->checkerService->getStatistics();

        return view('secondary-sales.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(Request $request)
    {
        // Get available tickets (optionally filter by a specific ticket)
        $ticketId = $request->input('ticket_id');
        $selectedTicket = $ticketId ? SecondaryLotteryTicket::find($ticketId) : null;
        
        $tickets = SecondaryLotteryTicket::doesntHave('transactions')->latest()->take(100)->get();
        
        // Get customers for searchable dropdown (name + phone)
        $customers = Customer::select('id', 'full_name', 'phone_number')
            ->orderBy('full_name')
            ->take(200)
            ->get();

        return view('secondary-sales.transactions.create', compact('tickets', 'selectedTicket', 'customers'));
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $saleType = $request->input('sale_type', 'own');
        
        $rules = [
            'secondary_ticket_id' => 'required|uuid|exists:secondary_lottery_tickets,id',
            'amount_thb' => 'nullable|numeric|min:0',
            'amount_mmk' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'sale_type' => 'required|in:own,other',
            'is_paid' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ];

        // Only validate customer fields if sale_type is 'own'
        if ($saleType === 'own') {
            $rules['customer_id'] = 'nullable|exists:customers,id';
            // Phone is now optional, Name is required if creating new
            $rules['customer_phone'] = 'nullable|string|max:20';
            $rules['customer_name'] = 'nullable|string|max:255';
        }

        $validator = \Validator::make($request->all(), $rules);

        // Ensure at least one amount is provided
        $validator->after(function ($validator) use ($request) {
            if (!$request->amount_thb && !$request->amount_mmk) {
                $validator->errors()->add('amount_thb', 'Please provide either Amount (THB) or Amount (MMK).');
            }
            // If creating new customer (no ID provided), name is required
            if ($request->sale_type === 'own' && !$request->customer_id && !$request->customer_phone && !$request->customer_name) {
                 $validator->errors()->add('customer_name', 'Customer Name or Phone is required.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customerId = $request->input('customer_id');
        $customerName = null;
        $customerPhone = null;
        $publicToken = null;
        
        // Handle customer info only for 'own' sales
        if ($saleType === 'own') {
            $customerName = $request->input('customer_name');
            $customerPhone = $request->input('customer_phone');
            
            // If customer_id is provided, use existing customer
            if ($customerId) {
                $existingCustomer = Customer::find($customerId);
                if ($existingCustomer) {
                    $customerName = $existingCustomer->full_name;
                    $customerPhone = $existingCustomer->phone_number;
                }
            } elseif ($request->filled('customer_phone')) {
                // Try to find existing customer by phone
                $existingCustomer = Customer::where('phone_number', $request->customer_phone)->first();
                
                if ($existingCustomer) {
                    $customerId = $existingCustomer->id;
                } elseif ($request->input('create_customer') === 'yes') {
                    // Create new customer with phone
                    $newCustomer = Customer::create([
                        'phone_number' => $request->customer_phone,
                        'full_name' => $request->customer_name,
                        'password' => bcrypt('password123'), // Default password
                    ]);
                    $customerId = $newCustomer->id;
                }
            } elseif ($request->filled('customer_name') && $request->input('create_customer') === 'yes') {
                 // Create new customer with Name ONLY (no phone)
                 // We need to handle nullable phone now
                 $newCustomer = Customer::create([
                    'full_name' => $request->customer_name,
                    'phone_number' => null, 
                    'password' => bcrypt('password123'),
                 ]);
                 $customerId = $newCustomer->id;
            }
            
            // Generate unique public token for result checking link
            $publicToken = SecondarySalesTransaction::generatePublicToken();
        }

        $transaction = SecondarySalesTransaction::create([
            'transaction_number' => SecondarySalesTransaction::generateTransactionNumber(),
            'sale_type' => $saleType,
            'secondary_ticket_id' => $request->secondary_ticket_id,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'purchased_at' => $request->purchased_at,
            'amount_thb' => $request->amount_thb,
            'amount_mmk' => $request->amount_mmk,
            'is_paid' => $request->boolean('is_paid'),
            'payment_method' => $request->is_paid ? $request->payment_method : null,
            'payment_date' => $request->boolean('is_paid') ? now() : null,
            'notes' => $request->notes,
            'public_token' => $publicToken,
            'created_by' => auth()->id(),
        ]);

        // Generate or reuse batch_token for customer+withdraw_date combination
        // This ensures ONE link per customer per draw date
        if ($saleType === 'own') {
            $ticket = $transaction->secondaryTicket()->first();
            $withdrawDate = $ticket?->withdraw_date?->format('Y-m-d');
            
            // Must have either customer_id or customerPhone, AND a withdraw_date
            $hasCustomerIdentifier = $customerId || $customerPhone;
            
            if ($hasCustomerIdentifier && $withdrawDate) {
                // Build query to find existing batch_token for same customer + same draw
                $query = SecondarySalesTransaction::whereHas('secondaryTicket', function($q) use ($withdrawDate) {
                    $q->whereDate('withdraw_date', $withdrawDate);
                })
                ->whereNotNull('batch_token')
                ->where('id', '!=', $transaction->id);
                
                // Match by customer_id if available, otherwise by phone
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('customer_phone', $customerPhone);
                }
                
                $existingBatchToken = $query->latest()->value('batch_token');
                
                if ($existingBatchToken) {
                    $transaction->batch_token = $existingBatchToken;
                } else {
                    // Generate new batch_token for this customer+draw combination
                    $transaction->batch_token = Str::random(32);
                }
                $transaction->save();
            }
        }

        $message = 'Transaction created successfully! #' . $transaction->transaction_number;
        
        // Add batch link info for 'own' sales
        if ($saleType === 'own' && $transaction->batch_token) {
            $batchLink = route('public.customer-batch', ['token' => $transaction->batch_token]);
            $message .= ' <br><small>📱 Batch Link: <a href="' . $batchLink . '" target="_blank" class="text-primary">' . $batchLink . '</a></small>';
        } elseif ($saleType === 'own' && $publicToken) {
            $message .= ' <br><small>📱 Public Link: <a href="' . $transaction->public_result_url . '" target="_blank" class="text-primary">' . $transaction->public_result_url . '</a></small>';
        }

        return redirect()->route('secondary-transactions.index')
                         ->with('success', $message);
    }

    /**
     * Display the specified transaction
     */
    public function show(SecondarySalesTransaction $secondaryTransaction)
    {
        $secondaryTransaction->load(['secondaryTicket', 'customer', 'drawResult', 'createdBy']);
        
        return view('secondary-sales.transactions.show', compact('secondaryTransaction'));
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(SecondarySalesTransaction $secondaryTransaction)
    {
        $secondaryTransaction->load(['secondaryTicket', 'customer']);
        $tickets = SecondaryLotteryTicket::latest()->take(50)->get();
        
        return view('secondary-sales.transactions.edit', compact('secondaryTransaction', 'tickets'));
    }

    /**
     * Update the specified transaction
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
                $validator->errors()->add('amount_thb', 'Please provide either Amount (THB) or Amount (MMK).');
            }
        });

        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
        }

        $secondaryTransaction->update([
            'secondary_ticket_id' => $request->secondary_ticket_id,
            'amount_thb' => $request->amount_thb,
            'amount_mmk' => $request->amount_mmk,
            'purchased_at' => $request->purchased_at,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'is_paid' => $request->boolean('is_paid'),
            'payment_method' => $request->is_paid ? $request->payment_method : null,
            'notes' => $request->notes,
        ]);

        // If marking as paid, set payment date
        if ($request->boolean('is_paid') && !$secondaryTransaction->payment_date) {
            $secondaryTransaction->update(['payment_date' => now()]);
        }


        return redirect()->route('secondary-transactions.index')
                         ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction
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
    public function checkResultsPage()
    {
        try {
            $stats = $this->checkerService->getStatistics();
            $statusGroups = $this->checkerService->getTransactionsByDrawStatus();
            
            // Paginate ready to check
            $readyToCheckCollection = $statusGroups['ready_to_check'];
            $perPage = 20;
            $currentPage = request()->get('page', 1);
            $readyToCheck = new \Illuminate\Pagination\LengthAwarePaginator(
                $readyToCheckCollection->forPage($currentPage, $perPage),
                $readyToCheckCollection->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $recentWinners = SecondarySalesTransaction::with(['customer', 'secondaryTicket', 'drawResult'])
                ->where('status', 'won')
                ->latest('checked_at')
                ->take(10)
                ->get();

            return view('secondary-sales.transactions.check-results', compact(
                'stats', 
                'readyToCheck', 
                'statusGroups',
                'recentWinners'
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

        // Generate CSV
        $filename = 'secondary_sales_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'Transaction #',
                'Lottery Number',
                'Customer Name',
                'Customer Phone',
                'Purchase Date',
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
}
