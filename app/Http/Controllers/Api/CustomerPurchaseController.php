<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryTicket;
use App\Models\TicketPurchase;
use App\Mail\NewTicketPurchase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerPurchaseController extends Controller
{
    /**
     * Get available lottery tickets (Public)
     */
    public function availableTickets()
    {
        $tickets = LotteryTicket::available()
                                ->select('id', 'ticket_name', 'ticket_type', 'numbers', 
                                        'bar_code', 'period', 'price', 'withdraw_date', 
                                        'stock', 'left_icon')
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Purchase a ticket (Authenticated)
     */
    public function purchase(Request $request)
    {
        // Get authenticated customer via Sanctum
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'lottery_ticket_id' => 'required|exists:lottery_tickets,id',
            'quantity' => 'required|integer|min:1',
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for existing pending/approved purchase of same ticket by this customer
        $existing = TicketPurchase::where('customer_id', $customer->id)
            ->where('lottery_ticket_id', $request->lottery_ticket_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending or approved purchase for this ticket!',
            ], 409);
        }

        // Start DB transaction
        \DB::beginTransaction();

        // Lock the ticket row for update to avoid race conditions
        $ticket = LotteryTicket::where('id', $request->lottery_ticket_id)->lockForUpdate()->first();

        if (!$ticket || $ticket->status !== 'active' || $ticket->stock < $request->quantity) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ticket not available or insufficient stock.',
            ], 400);
        }

        // Upload payment screenshot
        $path = $request->file('payment_screenshot')->store('payment_screenshots', 'public');

        // Create purchase
        $purchase = TicketPurchase::create([
            'customer_id' => $customer->id,
            'lottery_ticket_id' => $ticket->id,
            'order_number' => TicketPurchase::generateOrderNumber(),
            'quantity' => $request->quantity,
            'total_price' => $ticket->price * $request->quantity,
            'payment_screenshot' => $path,
            'status' => 'pending',
        ]);

        // (OPTIONAL: Only decrement stock on admin approval—in real e-lottery. If you want to hold stock now, uncomment next line)
        // $ticket->decrement('stock', $request->quantity);

        \DB::commit();

        // Email admins (use send to check on development; use queue if running background worker)
        $admins = \App\Models\User::permission('payment-approve')->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                \Mail::to($admin->email)->send(new \App\Mail\NewTicketPurchase($purchase));
                // Use ->queue(...) in production with queue worker
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase submitted successfully. Waiting for admin approval.',
            'data' => [
                'order_number' => $purchase->order_number,
                'status' => $purchase->status,
                'total_price' => $purchase->total_price,
                'created_at' => $purchase->created_at,
            ]
        ], 201);
    }

    /**
     * Get customer purchase history (Authenticated)
     */
    public function myPurchases(Request $request)
    {
        $customer = $request->user();

        $purchases = TicketPurchase::with('lotteryTicket')
                                   ->where('customer_id', $customer->id)
                                   ->latest()
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => $purchases,
        ]);
    }

    /**
     * Get customer approved tickets (Authenticated)
     */
    public function myTickets(Request $request)
    {
        $customer = $request->user();

        $tickets = TicketPurchase::with('lotteryTicket')
                                 ->where('customer_id', $customer->id)
                                 ->approved()
                                 ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }
}
