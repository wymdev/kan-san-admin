<?php

namespace App\Http\Controllers;

use App\Models\SecondarySalesTransaction;
use Illuminate\Http\Request;

class PublicResultController extends Controller
{
    /**
     * Show public lottery result check page
     * This is accessible without authentication
     */
    public function show(string $token)
    {
        $transaction = SecondarySalesTransaction::with(['secondaryTicket', 'drawResult'])
            ->where('public_token', $token)
            ->first();

        if (!$transaction) {
            abort(404, 'Lottery ticket not found');
        }

        return view('public.lottery-result', [
            'transaction' => $transaction,
            'ticket' => $transaction->secondaryTicket,
            'drawResult' => $transaction->drawResult,
        ]);
    }
}
