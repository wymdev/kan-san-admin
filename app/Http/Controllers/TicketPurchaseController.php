<?php

namespace App\Http\Controllers;

use App\Models\TicketPurchase;
use Illuminate\Http\Request;
use App\Services\PushNotificationService;
use App\Services\LotteryResultCheckerService;

class TicketPurchaseController extends Controller
{
    protected $pushService;
    protected $checkerService;

    public function __construct(
        PushNotificationService $pushService,
        LotteryResultCheckerService $checkerService
    ) {
        $this->middleware('permission:payment-check', ['only' => ['index', 'show']]);
        $this->middleware('permission:payment-approve', ['only' => ['approve', 'reject']]);
        $this->middleware('permission:lottery-check', ['only' => ['checkResults', 'bulkNotifyResults']]);
        
        $this->pushService = $pushService;
        $this->checkerService = $checkerService;
    }

    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $query = TicketPurchase::with(['customer', 'lotteryTicket', 'approvedBy', 'drawResult']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by result status (won/not_won/unchecked)
        if ($request->input('result_status') === 'won') {
            $query->where('status', 'won');
        } elseif ($request->input('result_status') === 'not_won') {
            $query->where('status', 'not_won');
        } elseif ($request->input('result_status') === 'unchecked') {
            $query->where('status', 'approved')->whereNull('checked_at');
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
        
        // Get statistics
        $stats = $this->checkerService->getStatistics();
        
        return view('tickets.purchases.index', compact('purchases', 'stats'));
    }

    /**
     * Display the specified purchase
     */
    public function show(TicketPurchase $purchase)
    {
        $purchase->load(['customer', 'lotteryTicket', 'approvedBy', 'drawResult']);
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
        if ($ticket && $ticket->stock > 0) {
            $ticket->decrement('stock', $purchase->quantity);
        }

        // Send push notification to Customer
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

            $this->pushService->sendSingleNotification($message, $customer->id);
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

            $this->pushService->sendSingleNotification($message, $customer->id);
        }

        return redirect()->route('purchases.index')
                         ->with('success', 'Purchase rejected.');
    }

    /**
     * Check all approved purchases against latest lottery results
     */
    public function checkResults()
    {
        try {
            $result = $this->checkerService->checkAllPendingPurchases();

            // Return with appropriate flash message type
            $flashType = $result['type'] ?? 'info'; // success, error, warning, info
            
            return redirect()->back()->with($flashType, $result['message']);
            
        } catch (\Exception $e) {
            \Log::error('Check Results Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', '❌ System Error: ' . $e->getMessage());
        }
    }

    /**
     * Send bulk notifications to all customers about their lottery results
     */
    public function bulkNotifyResults()
    {
        try {
            // Get recently checked purchases (checked within last 2 hours)
            $recentlyChecked = TicketPurchase::with(['customer', 'lotteryTicket', 'drawResult'])
                ->whereNotNull('checked_at')
                ->where('checked_at', '>=', now()->subHours(2))
                ->whereIn('status', ['won', 'not_won'])
                ->get();

            if ($recentlyChecked->isEmpty()) {
                return redirect()->back()->with('warning', '⚠️ No recently checked purchases found. Please run "Check All Results" first.');
            }

            $sentCount = 0;
            $failedCount = 0;
            $winners = [];

            foreach ($recentlyChecked as $purchase) {
                $customer = $purchase->customer;
                
                if (!$customer || !$customer->expo_push_token) {
                    $failedCount++;
                    continue;
                }

                if ($purchase->status === 'won') {
                    $title = '🎊 CONGRATULATIONS! You Won!';
                    $body = "Your ticket #{$purchase->order_number} won {$purchase->prize_won}! 🎉";
                    $type = 'lottery_won';
                    $winners[] = $purchase->order_number;
                } else {
                    $title = '📋 Lottery Results';
                    $body = "Results are in for your ticket #{$purchase->order_number}. Check details in the app.";
                    $type = 'lottery_result';
                }

                $message = [
                    'to' => $customer->expo_push_token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => [
                        'purchase_id' => $purchase->id,
                        'type' => $type,
                        'order_number' => $purchase->order_number,
                        'status' => $purchase->status,
                        'prize_won' => $purchase->prize_won,
                        'draw_date' => $purchase->drawResult?->date_en,
                        'screen' => 'PurchaseDetails',
                    ],
                    'badge' => 1,
                    'priority' => 'high',
                    'channelId' => 'default',
                ];

                $sent = $this->pushService->sendSingleNotification($message, $customer->id);
                
                if ($sent) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }

                // Small delay between notifications
                usleep(100000); // 0.1 second
            }

            $message = "📢 Successfully sent <strong>{$sentCount}</strong> notification(s)";
            
            if (count($winners) > 0) {
                $message .= " | 🎉 <strong>" . count($winners) . "</strong> winner(s) notified";
            }
            
            if ($failedCount > 0) {
                $message .= " | ⚠️ <strong>{$failedCount}</strong> failed (no push token)";
            }

            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Bulk Notify Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error sending notifications: ' . $e->getMessage());
        }
    }

    /**
     * Display check results page
     */
    public function checkResultsPage()
    {
        try {
            $stats = $this->checkerService->getStatistics();
            $statusGroups = $this->checkerService->getPurchasesByDrawStatus();
            
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

            $recentWinners = TicketPurchase::with(['customer', 'lotteryTicket', 'drawResult'])
                ->where('status', 'won')
                ->latest('checked_at')
                ->take(10)
                ->get();

            return view('tickets.purchases.check-results', compact(
                'stats', 
                'readyToCheck', 
                'statusGroups',
                'recentWinners'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Check Results Page Error: ' . $e->getMessage());
            return redirect()->route('purchases.index')
                           ->with('error', '❌ Error loading check results page: ' . $e->getMessage());
        }
    }
}