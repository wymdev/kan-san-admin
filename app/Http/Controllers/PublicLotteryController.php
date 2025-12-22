<?php

namespace App\Http\Controllers;

use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use App\Models\DrawResult;
use Illuminate\Http\Request;

class PublicLotteryController extends Controller
{
    /**
     * Main public lottery checking page
     */
    public function index(Request $request)
    {
        $drawDates = DrawResult::orderBy('draw_date', 'desc')->take(24)->get();
        
        // Check if specific date requested
        if ($request->has('date')) {
            $selectedDraw = DrawResult::whereDate('draw_date', $request->date)->first();
        } else {
            $selectedDraw = DrawResult::latest('draw_date')->first();
        }
        
        return view('public.lottery-check', [
            'latestDraw' => $selectedDraw,
            'drawDates' => $drawDates,
        ]);
    }

    /**
     * Check multiple lottery numbers via AJAX
     */
    public function check(Request $request)
    {
        $request->validate([
            'numbers' => 'required|string',
            'draw_date' => 'nullable|date',
        ]);

        $inputNumbers = $request->input('numbers');
        $drawDate = $request->input('draw_date');

        // Parse input numbers (comma, newline, or space separated)
        $numbers = preg_split('/[\s,\n]+/', $inputNumbers);
        $numbers = array_filter(array_map(function($n) {
            return preg_replace('/\D/', '', trim($n));
        }, $numbers));
        $numbers = array_filter($numbers, fn($n) => strlen($n) === 6);
        $numbers = array_unique($numbers);

        if (empty($numbers)) {
            return response()->json([
                'success' => false,
                'error' => 'กรุณากรอกหมายเลขลอตเตอรี่ที่ถูกต้อง (6 หลัก)',
            ]);
        }

        // Get draw result
        if ($drawDate) {
            $drawResult = DrawResult::whereDate('draw_date', $drawDate)->first();
        } else {
            $drawResult = DrawResult::latest('draw_date')->first();
        }

        if (!$drawResult) {
            return response()->json([
                'success' => false,
                'error' => 'ไม่พบผลรางวัลสำหรับงวดนี้',
            ]);
        }

        // Check each number
        $results = [];
        foreach ($numbers as $number) {
            $result = $this->checkNumber($number, $drawResult);
            $results[] = $result;
        }

        // Summary
        $wonCount = count(array_filter($results, fn($r) => $r['won']));
        $totalChecked = count($results);

        return response()->json([
            'success' => true,
            'draw_date' => $drawResult->date_th ?? $drawResult->date_en,
            'draw_date_en' => $drawResult->draw_date->format('Y-m-d'),
            'results' => $results,
            'summary' => [
                'total' => $totalChecked,
                'won' => $wonCount,
                'not_won' => $totalChecked - $wonCount,
            ],
        ]);
    }

    /**
     * Check a single number against draw result - supports multiple prizes
     */
    private function checkNumber(string $number, DrawResult $drawResult): array
    {
        $result = $drawResult->checkNumber($number);

        // Now $result is an array of wins, or false if no wins
        if ($result && is_array($result) && count($result) > 0) {
            // Check if it's the new format (array of prizes) or old format (single prize)
            $prizes = [];
            
            // New format: array of prize arrays
            if (isset($result[0]) && is_array($result[0])) {
                foreach ($result as $prize) {
                    $prizes[] = [
                        'name' => $prize['prize_name'] ?? 'Prize',
                        'reward' => isset($prize['reward']) ? number_format($prize['reward']) : '',
                    ];
                }
            } 
            // Old format (single prize object) - backwards compatibility
            elseif (isset($result['won']) && $result['won']) {
                $prizes[] = [
                    'name' => $result['prize_name'] ?? 'Prize',
                    'reward' => isset($result['reward']) ? number_format($result['reward']) : '',
                ];
            }
            
            if (count($prizes) > 0) {
                return [
                    'number' => $number,
                    'won' => true,
                    'prizes' => $prizes,
                ];
            }
        }

        return [
            'number' => $number,
            'won' => false,
            'prizes' => [],
        ];
    }

    /**
     * Show customer's batch tickets via unique link
     */
    public function customerBatch(string $token)
    {
        // Try batch_token first (new system - one link per customer+batch)
        $transactions = SecondarySalesTransaction::with(['secondaryTicket', 'drawResult', 'customer'])
            ->where('batch_token', $token)
            ->get();

        // Backward compatibility: try public_token (old system - one link per ticket)
        if ($transactions->isEmpty()) {
            $singleTransaction = SecondarySalesTransaction::with(['secondaryTicket', 'drawResult', 'customer'])
                ->where('public_token', $token)
                ->first();

            if ($singleTransaction) {
                // Get all transactions for same customer & batch/draw
                $batchNumber = $singleTransaction->secondaryTicket?->batch_number;
                $withdrawDate = $singleTransaction->secondaryTicket?->withdraw_date?->format('Y-m-d');
                $customerId = $singleTransaction->customer_id;
                $customerPhone = $singleTransaction->customer_phone;

                if ($batchNumber || $withdrawDate) {
                    $transactions = SecondarySalesTransaction::with(['secondaryTicket', 'drawResult'])
                        ->whereHas('secondaryTicket', function($q) use ($batchNumber, $withdrawDate) {
                            $q->where(function($query) use ($batchNumber, $withdrawDate) {
                                $query->where(function($sub_q) use ($withdrawDate) {
                                    if ($withdrawDate) {
                                        $sub_q->whereDate('withdraw_date', $withdrawDate);
                                    }
                                })->orWhere(function($sub_q) use ($batchNumber) {
                                    if ($batchNumber) {
                                        $sub_q->where('batch_number', $batchNumber);
                                    }
                                });
                            });
                        })
                        ->where(function($q) use ($customerId, $customerPhone) {
                            if ($customerId) {
                                $q->where('customer_id', $customerId);
                            }
                            if ($customerPhone) {
                                $q->orWhere('customer_phone', $customerPhone);
                            }
                        })
                        ->get();
                } else {
                    $transactions = collect([$singleTransaction]);
                }
            }
        }

        if ($transactions->isEmpty()) {
            abort(404, 'ไม่พบข้อมูลสลาก');
        }

        // Get batch info
        $batchNumber = $transactions->first()->secondaryTicket?->batch_number ?? 'N/A';
        $customerName = $transactions->first()->customer_display_name;

        // Calculate stats
        $stats = [
            'total' => $transactions->count(),
            'won' => $transactions->where('status', 'won')->count(),
            'not_won' => $transactions->where('status', 'not_won')->count(),
            'pending' => $transactions->where('status', 'pending')->count(),
        ];

        // Group by status for display
        $winners = $transactions->where('status', 'won');
        $pending = $transactions->where('status', 'pending');
        $notWon = $transactions->where('status', 'not_won');

        return view('public.customer-batch', [
            'transactions' => $transactions,
            'batchNumber' => $batchNumber,
            'customerName' => $customerName,
            'stats' => $stats,
            'winners' => $winners,
            'pending' => $pending,
            'notWon' => $notWon,
        ]);
    }

    /**
     * Get historical draw results
     */
    public function history(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        $results = DrawResult::whereYear('draw_date', $year)
            ->orderBy('draw_date', 'desc')
            ->get();

        $years = DrawResult::selectRaw('YEAR(draw_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('public.lottery-history', [
            'results' => $results,
            'selectedYear' => $year,
            'years' => $years,
        ]);
    }

    /**
     * Show specific draw result details
     */
    public function showResult(string $date)
    {
        $drawResult = DrawResult::whereDate('draw_date', $date)->firstOrFail();
        
        return view('public.lottery-result-detail', [
            'drawResult' => $drawResult,
        ]);
    }
}
