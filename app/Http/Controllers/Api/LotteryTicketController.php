<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;

class LotteryTicketController extends Controller
{
    /**
     * Public API: Get list of lottery tickets (requires X-API-KEY header).
     */
    public function index(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('services.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key',
            ], 401);
        }

        // Get IDs of tickets that are pending/approved (any customer)
        $purchasedOrPendingTicketIds = \App\Models\TicketPurchase::whereIn('status', ['pending', 'approved'])
            ->pluck('lottery_ticket_id')->unique()->toArray();

        // Exclude them from the results
        $tickets = LotteryTicket::whereDate('withdraw_date', '>=', now())
            ->whereNotIn('id', $purchasedOrPendingTicketIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }


}
