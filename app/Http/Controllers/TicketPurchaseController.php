<?php

namespace App\Http\Controllers;
use App\Models\TicketPurchase;
use Illuminate\Http\Request;
use App\Services\PushNotificationService;

class TicketPurchaseController extends Controller
{
    protected $pushService;
    public function __construct(PushNotificationService $pushService)
    {
        $this->middleware('permission:payment-check', ['only' => ['index', 'show']]);
        $this->middleware('permission:payment-approve', ['only' => ['approve', 'reject']]);
        $this->pushService = $pushService;
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

        // Send push notification to Customer if they have expo token
        $customer = $purchase->customer;
        if ($customer && $customer->expo_push_token) {
            $title = '🎉 Purchase Approved!';
            $body = "Your lottery ticket purchase #{$purchase->order_number} has been approved. Quantity: {$purchase->quantity}";

            $message = [
                'to' => $customer->expo_push_token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => [
                    'purchase_id' => $purchase->id,
                    'type' => 'purchase_approved',
                    'order_number' => $purchase->order_number,
                    'screen' => 'PurchaseDetails',
                ],
                'badge' => 1,
                'priority' => 'high',
                'channelId' => 'default',
            ];

            // Send via the service
            $response = $this->pushService->sendSingleNotification($message, $customer->id);
            // Optionally check $response for success/failure
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

        // Send notification to the customer
        $customer = $purchase->customer;
        if ($customer && $customer->expo_push_token) {
            $title = '❌ Purchase Rejected';
            $body = "Your lottery ticket purchase #{$purchase->order_number} was rejected. Tap to view reason.";

            $message = [
                'to' => $customer->expo_push_token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => [
                    'purchase_id' => $purchase->id,
                    'type' => 'purchase_rejected',
                    'order_number' => $purchase->order_number,
                    'rejection_reason' => $purchase->rejection_reason,
                    'screen' => 'PurchaseDetails',
                ],
                'badge' => 1,
                'priority' => 'high',
                'channelId' => 'default',
            ];

            $response = $this->pushService->sendSingleNotification($message, $customer->id);
        }

        return redirect()->route('purchases.index')
                         ->with('success', 'Purchase rejected.');
    }
}
