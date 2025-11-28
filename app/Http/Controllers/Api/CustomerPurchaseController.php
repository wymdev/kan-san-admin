<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryTicket;
use App\Models\TicketPurchase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerPurchaseController extends Controller
{
    /**
     * Get available lottery tickets (Public)
     */
    public function availableTickets()
    {
        $tickets = LotteryTicket::available()
            ->select(
                'id',
                'ticket_name',
                'ticket_type',
                'numbers',
                'bar_code',
                'period',
                'price',
                'withdraw_date',
                'stock',
                'left_icon'
            )
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
        DB::beginTransaction();
        try {
            // Lock the ticket row for update to avoid race conditions
            $ticket = LotteryTicket::where('id', $request->lottery_ticket_id)->lockForUpdate()->first();

            if (!$ticket || $ticket->status !== 'active' || $ticket->stock < $request->quantity) {
                DB::rollBack();
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

            DB::commit();

            // ✅ CREATE ADMIN NOTIFICATION
            \App\Models\AdminNotification::createNewOrder($purchase, $customer);

            // Notify admins with permission
            $admins = \App\Models\User::permission('payment-approve')->get();
            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)->send(new \App\Mail\NewTicketPurchase($purchase));
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

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing purchase: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer purchase history (Authenticated)
     * Returns all purchases with full details including win status
     */
    public function myPurchases(Request $request)
    {
        $customer = $request->user();

        $purchases = TicketPurchase::with(['lotteryTicket', 'drawResult'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        // Transform purchases to include win information
        $transformedPurchases = $purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'order_number' => $purchase->order_number,
                'quantity' => $purchase->quantity,
                'total_price' => $purchase->total_price,
                'status' => $purchase->status,
                'payment_screenshot' => $purchase->payment_screenshot,
                'rejection_reason' => $purchase->rejection_reason,
                'approved_at' => $purchase->approved_at,
                'created_at' => $purchase->created_at,
                'checked_at' => $purchase->checked_at,
                'prize_won' => $purchase->prize_won,
                'is_winner' => $purchase->status === 'won',
                'lottery_ticket' => $purchase->lotteryTicket ? [
                    'id' => $purchase->lotteryTicket->id,
                    'ticket_name' => $purchase->lotteryTicket->ticket_name,
                    'ticket_type' => $purchase->lotteryTicket->ticket_type,
                    'numbers' => $purchase->lotteryTicket->numbers,
                    'bar_code' => $purchase->lotteryTicket->bar_code,
                    'period' => $purchase->lotteryTicket->period,
                    'price' => $purchase->lotteryTicket->price,
                    'withdraw_date' => $purchase->lotteryTicket->withdraw_date,
                    'left_icon' => $purchase->lotteryTicket->left_icon,
                ] : null,
                'draw_result' => $purchase->drawResult ? [
                    'id' => $purchase->drawResult->id,
                    'date_en' => $purchase->drawResult->date_en,
                    'period' => $purchase->drawResult->period,
                ] : null,
            ];
        });

        // Calculate statistics
        $stats = [
            'total_purchases' => $purchases->count(),
            'pending_purchases' => $purchases->where('status', 'pending')->count(),
            'approved_purchases' => $purchases->where('status', 'approved')->count(),
            'rejected_purchases' => $purchases->where('status', 'rejected')->count(),
            'won_purchases' => $purchases->where('status', 'won')->count(),
            'not_won_purchases' => $purchases->where('status', 'not_won')->count(),
            'unchecked_purchases' => $purchases->where('status', 'approved')->whereNull('checked_at')->count(),
            'total_prize_amount' => $purchases->where('status', 'won')->sum('prize_won'),
            'total_spent' => $purchases->whereIn('status', ['approved', 'won', 'not_won'])->sum('total_price'),
        ];

        return response()->json([
            'success' => true,
            'data' => $transformedPurchases,
            'statistics' => $stats,
        ]);
    }

    /**
     * Get single purchase detail (Authenticated)
     */
    public function purchaseDetail(Request $request, $id)
    {
        $customer = $request->user();

        $purchase = TicketPurchase::with(['lotteryTicket', 'drawResult', 'approvedBy'])
            ->where('customer_id', $customer->id)
            ->find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found or you do not have permission to view it.',
            ], 404);
        }

        // Transform purchase to include all details
        $data = [
            'id' => $purchase->id,
            'order_number' => $purchase->order_number,
            'quantity' => $purchase->quantity,
            'total_price' => $purchase->total_price,
            'status' => $purchase->status,
            'payment_screenshot' => $purchase->payment_screenshot ? url('storage/' . $purchase->payment_screenshot) : null,
            'rejection_reason' => $purchase->rejection_reason,
            'approved_at' => $purchase->approved_at,
            'created_at' => $purchase->created_at,
            'checked_at' => $purchase->checked_at,
            'prize_won' => $purchase->prize_won,
            'is_winner' => $purchase->status === 'won',
            'lottery_ticket' => $purchase->lotteryTicket ? [
                'id' => $purchase->lotteryTicket->id,
                'ticket_name' => $purchase->lotteryTicket->ticket_name,
                'ticket_type' => $purchase->lotteryTicket->ticket_type,
                'numbers' => $purchase->lotteryTicket->numbers,
                'bar_code' => $purchase->lotteryTicket->bar_code,
                'period' => $purchase->lotteryTicket->period,
                'price' => $purchase->lotteryTicket->price,
                'withdraw_date' => $purchase->lotteryTicket->withdraw_date,
                'left_icon' => $purchase->lotteryTicket->left_icon,
            ] : null,
            'draw_result' => $purchase->drawResult ? [
                'id' => $purchase->drawResult->id,
                'date_en' => $purchase->drawResult->date_en,
                'period' => $purchase->drawResult->period,
            ] : null,
            'approved_by' => $purchase->approvedBy ? [
                'id' => $purchase->approvedBy->id,
                'name' => $purchase->approvedBy->name,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get customer approved tickets only (Authenticated)
     */
    public function myTickets(Request $request)
    {
        $customer = $request->user();

        $tickets = TicketPurchase::with(['lotteryTicket', 'drawResult'])
            ->where('customer_id', $customer->id)
            ->approved()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }
}
