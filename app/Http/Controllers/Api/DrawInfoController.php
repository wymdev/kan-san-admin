<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DrawInfo;
use Illuminate\Http\Request;

class DrawInfoController extends Controller
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

        $now = now();
        $draw = DrawInfo::where('draw_date', '>=', $now)->orderBy('draw_date', 'asc')->first();
        if (!$draw) {
            return response()->json(['message' => 'Not Data found']);
        }

        return response()->json([
            'draw_date' => $draw->draw_date,
            'result_announce_date' => $draw->result_announce_date,
            'is_estimated' => $draw->is_estimated,
            'note' => $draw->note,
            'period' => $draw->period,
        ]);
    }

}
