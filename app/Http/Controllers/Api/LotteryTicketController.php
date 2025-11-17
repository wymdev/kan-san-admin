<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryTicket;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LotteryTicketController extends Controller
{
    /**
     * Public API: Get list of lottery tickets (requires X-API-KEY header).
     * Tickets are only available from 10 AM (Thailand time) on their withdraw_date onwards.
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

        // Get current time in Thailand timezone
        $nowThailand = Carbon::now('Asia/Bangkok');
        $todayThailand = $nowThailand->toDateString();
        $currentHourThailand = $nowThailand->hour;

        $tickets = LotteryTicket::where(function($query) use ($todayThailand, $currentHourThailand) {
                // Show tickets with withdraw_date in the future
                $query->whereDate('withdraw_date', '>', $todayThailand)
                
                // OR show tickets with withdraw_date = today AND current hour >= 10
                ->orWhere(function($q) use ($todayThailand, $currentHourThailand) {
                    $q->whereDate('withdraw_date', '=', $todayThailand);
                    
                    // Only include if current hour is >= 10
                    if ($currentHourThailand >= 10) {
                        // Do nothing, condition already met
                    } else {
                        // Exclude today's tickets if before 10 AM
                        $q->whereRaw('1 = 0'); // Always false condition
                    }
                });
            })
            ->whereNotIn('id', $purchasedOrPendingTicketIds)
            ->where('status', 'active')
            ->orderBy('withdraw_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'server_time' => $nowThailand->format('Y-m-d H:i:s'),
            'timezone' => 'Asia/Bangkok',
        ]);
    }
}