<?php

namespace App\Http\Controllers;

use App\Models\TicketPurchase;
use Illuminate\Http\Request;

class TicketPurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment-check', ['only' => ['index', 'show']]);
        $this->middleware('permission:payment-approve', ['only' => ['approve', 'reject']]);
    }

    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $query = TicketPurchase::with(['customer', 'lotteryTicket', 'approvedBy']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('full_name', 'like', "%$search%")
                         ->orWhere('phone_number', 'like', "%$search%");
                  });
            });
        }

        $purchases = $query->latest()->paginate(20)->appends($request->query());
        return view('tickets.purchases.index', compact('purchases'));
    }

    /**
     * Display the specified purchase
     */
    public function show(TicketPurchase $purchase)
    {
        $purchase->load(['customer', 'lotteryTicket', 'approvedBy']);
        return view('tickets.purchases.show', compact('purchase'));
    }

    /**
     * Approve a purchase
     */
    public function approve(TicketPurchase $purchase)
    {
        if ($purchase->status !== 'pending') {
            return redirect()->back()->with('error', 'This purchase has already been processed.');
        }

        $purchase->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Decrease stock
        $ticket = $purchase->lotteryTicket;
        if ($ticket->stock > 0) {
            $ticket->decrement('stock', $purchase->quantity);
        }

        return redirect()->route('purchases.index')
                         ->with('success', 'Purchase approved successfully!');
    }

    /**
     * Reject a purchase
     */
    public function reject(Request $request, TicketPurchase $purchase)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($purchase->status !== 'pending') {
            return redirect()->back()->with('error', 'This purchase has already been processed.');
        }

        $purchase->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('purchases.index')
                         ->with('success', 'Purchase rejected.');
    }
}
