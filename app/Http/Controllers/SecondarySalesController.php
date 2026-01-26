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
use App\Services\SecondarySalesService;

class SecondarySalesController extends Controller
{
    protected $checkerService;
    protected $salesService;

    public function __construct(LotteryResultCheckerService $checkerService, SecondarySalesService $salesService)
    {
        $this->checkerService = $checkerService;
        $this->salesService = $salesService;
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
    /**
     * Store a newly created resource in storage.
     * Supports multiple ticket selection with shared batch link.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secondary_ticket_ids' => 'required|array|min:1',
            'secondary_ticket_ids.*' => 'required|uuid|exists:secondary_lottery_tickets,id',
            'amount_thb' => 'nullable|numeric|min:0',
            'amount_mmk' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
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
            // Customer required: either select from dropdown OR enter name/phone
            if (!$request->filled('customer_id') && !$request->filled('customer_name') && !$request->filled('customer_phone')) {
                $validator->errors()->add('customer_name', 'Select a customer or enter name/phone');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $ticketIds = $request->secondary_ticket_ids;

            // Handle customer using service
            $customer = $this->salesService->findOrCreateCustomer([
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
            ], $request->boolean('create_customer', false));

            $customerId = $customer?->id;
            $customerName = $customer?->full_name ?? $request->customer_name;
            $customerPhone = $customer?->phone_number ?? $request->customer_phone;

            // Generate or reuse batch_token based on customer + draw date
            // Find if customer already has transactions for the same draw date
            $firstTicket = SecondaryLotteryTicket::find($ticketIds[0]);
            $drawDate = $firstTicket?->withdraw_date;

            $existingBatchToken = null;
            if ($customerId && $drawDate) {
                // Look for existing transactions with same customer and draw date
                $existingTransaction = SecondarySalesTransaction::where('customer_id', $customerId)
                    ->whereHas('secondaryTicket', function ($q) use ($drawDate) {
                        $q->whereDate('withdraw_date', $drawDate);
                    })
                    ->whereNotNull('batch_token')
                    ->first();

                if ($existingTransaction) {
                    $existingBatchToken = $existingTransaction->batch_token;
                }
            }

            // Use existing batch_token or generate new one
            $sharedBatchToken = $existingBatchToken ?? Str::random(32);
            $transactions = [];

            // Calculate amount per ticket (split evenly)
            $ticketCount = count($ticketIds);
            $amountThbPerTicket = $request->amount_thb ? round($request->amount_thb / $ticketCount, 2) : 0;
            $amountMmkPerTicket = $request->amount_mmk ? round($request->amount_mmk / $ticketCount, 2) : 0;

            foreach ($ticketIds as $ticketId) {
                $ticket = SecondaryLotteryTicket::find($ticketId);
                if (!$ticket) {
                    throw new \Exception('Ticket not found: ' . $ticketId);
                }

                $transaction = SecondarySalesTransaction::create([
                    'transaction_number' => $this->salesService->generateTransactionNumber(),
                    'secondary_ticket_id' => $ticketId,
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'purchased_at' => $request->purchased_at,
                    'amount_thb' => $amountThbPerTicket,
                    'amount_mmk' => $amountMmkPerTicket,
                    'is_paid' => $request->boolean('is_paid', false),
                    'payment_method' => $request->payment_method,
                    'payment_date' => $request->is_paid ? now() : null,
                    'notes' => $request->notes,
                    'sale_type' => 'own',
                    'created_by' => auth()->id(),
                    'public_token' => Str::random(32), // Unique per ticket
                    'batch_token' => $sharedBatchToken, // Same for customer + draw date
                ]);

                $transactions[] = $transaction;
            }

            DB::commit();

            // Generate success message with batch link
            $ticketCount = count($transactions);
            $ticketNumbers = collect($transactions)->map(fn($t) => $t->secondaryTicket->ticket_number ?? 'N/A')->join(', ');
            $batchLink = route('public.customer-batch', ['token' => $sharedBatchToken]);

            $batchStatus = $existingBatchToken ? '(Added to existing batch)' : '(New batch created)';
            $message = "✅ {$ticketCount} ticket(s) sold to <strong>{$customerName}</strong>! {$batchStatus}";
            $message .= "<br><small>Tickets: {$ticketNumbers}</small>";
            $message .= "<br><br>📱 <strong>Batch Link:</strong> <a href=\"{$batchLink}\" target=\"_blank\" class=\"text-primary\">{$batchLink}</a>";

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
            if ($request->filled('customer_name') || $request->filled('customer_phone')) {
                // Use service to find or create
                $customer = $this->salesService->findOrCreateCustomer([
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                ], true); // Always create if not found in update context if needed, or refine logic?

                // NOTE: The original logic allowed updating customer details. 
                // The service mainly finds or creates. 
                // We should keep the update logic here or move it to service too?
                // For now, let's keep the update logic simplified or reuse the service carefully.
                // Actually, original logic *updates* the customer if found. Service just finds/creates.

                if ($customer) {
                    // Update existing customer info if provided
                    $updateData = [];
                    if ($request->filled('customer_name') && $customer->full_name !== $request->customer_name) {
                        $updateData['full_name'] = $request->customer_name;
                    }
                    if ($request->filled('customer_phone') && $customer->phone_number !== $request->customer_phone) {
                        $updateData['phone_number'] = $request->customer_phone;
                    }
                    if (!empty($updateData)) {
                        $customer->update($updateData);
                    }
                    $customerId = $customer->id;
                }
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
        return $this->salesService->exportTransactions($transactions);
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