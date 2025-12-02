<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DrawResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DrawResultApiController extends Controller
{
    private $nameMapping = [
        "รางวัลที่ 1" => "First Prize",
        "รางวัลข้างเคียงรางวัลที่ 1" => "1st Prize Neighbor",
        "รางวัลที่ 2" => "Second Prize",
        "รางวัลที่ 3" => "Third Prize",
        "รางวัลที่ 4" => "Fourth Prize",
        "รางวัลที่ 5" => "Fifth Prize",
        "รางวัลเลขหน้า 3 ตัว" => "Front Three Digits",
        "รางวัลเลขท้าย 3 ตัว" => "Back Three Digits",
        "รางวัลเลขท้าย 2 ตัว" => "Back Two Digits",
    ];

    private $prizeOrder = [
        "First Prize" => 1,
        "1st Prize Neighbor" => 2,
        "Front Three Digits" => 3,
        "Back Three Digits" => 4,
        "Second Prize" => 5,
        "Third Prize" => 6,
        "Fourth Prize" => 7,
        "Fifth Prize" => 8,
        "Back Two Digits" => 9,
    ];

    private function validateApiKey(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('services.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key',
            ], 401);
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($error = $this->validateApiKey($request)) return $error;

        $query = DrawResult::query()
            ->whereDate('draw_date', '<=', Carbon::now()->toDateString())
            ->orderBy('draw_date', 'desc');

        if ($request->boolean('latest')) {
            $result = $query->first();
            return $result
                ? response()->json(['success' => true, 'data' => $this->formatDrawResult($result)])
                : response()->json(['success' => false, 'message' => 'No data found'], 404);
        }

        if ($request->filled('draw_date'))
            $query->whereDate('draw_date', $request->draw_date);

        $data = $query->paginate(15)->map(fn($r) => $this->formatDrawResult($r));
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function dates(Request $request)
    {
        if ($error = $this->validateApiKey($request)) return $error;
        
        // Get the latest draw date
        $latestDate = DrawResult::whereDate('draw_date', '<=', Carbon::now()->toDateString())
            ->orderBy('draw_date', 'desc')
            ->first(['draw_date']);
        
        // Get all dates except the latest
        $query = DrawResult::whereDate('draw_date', '<=', Carbon::now()->toDateString())
            ->orderBy('draw_date', 'desc');
        
        // Only exclude if a latest date exists
        if ($latestDate) {
            $query->where('draw_date', '<', $latestDate->draw_date);
        }
        
        $dates = $query->get(['draw_date']);
        
        $formatted = $dates->map(function($d) {
            $dateOnly = Carbon::parse($d->draw_date)->format('Y-m-d');
            return [
                'draw_date' => $dateOnly,
                'label' => Carbon::parse($d->draw_date)->format('d M Y')
            ];
        });
        
        return response()->json(['success' => true, 'dates' => $formatted]);
    }

    public function checkLottery(Request $request)
    {
        if ($error = $this->validateApiKey($request)) return $error;

        $validator = Validator::make($request->all(), [
            'lottery_number' => 'required_without:lottery_numbers|string|min:2|max:6',
            'lottery_numbers' => 'required_without:lottery_number|array',
            'lottery_numbers.*' => 'string|min:2|max:6',
            'draw_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lotteryNumbers = $request->has('lottery_numbers')
            ? $request->lottery_numbers
            : [$request->lottery_number];

        $query = DrawResult::query()
            ->whereDate('draw_date', '<=', Carbon::now()->toDateString())
            ->orderBy('draw_date', 'desc');

        if ($request->filled('draw_date')) {
            $query->whereDate('draw_date', $request->draw_date);
        }
        $drawResult = $query->first();

        if (!$drawResult) {
            return response()->json(['success' => false, 'message' => 'Draw not found'], 404);
        }

        $results = [];
        foreach ($lotteryNumbers as $lotteryNumber) {
            $matchedPrizes = $this->checkNumberAgainstPrizes($lotteryNumber, $drawResult);

            usort($matchedPrizes, function ($a, $b) {
                $orderA = $this->prizeOrder[$a['prize_name']] ?? 999;
                $orderB = $this->prizeOrder[$b['prize_name']] ?? 999;
                return $orderA <=> $orderB;
            });

            $results[] = [
                'lottery_number' => $lotteryNumber,
                'matched_prizes' => $matchedPrizes,
                'is_winner' => count($matchedPrizes) > 0,
            ];
        }

        return response()->json([
            'success' => true,
            'draw_date' => Carbon::parse($drawResult->draw_date)->format('Y-m-d'),
            'results' => $results,
        ]);
    }

    private function checkNumberAgainstPrizes($lotteryNumber, $drawResult)
    {
        // Already decoded by model cast
        $prizes = is_array($drawResult->prizes) ? $drawResult->prizes : [];
        $runningNumbers = is_array($drawResult->running_numbers) ? $drawResult->running_numbers : [];
        $numberLength = strlen($lotteryNumber);
        $allPrizes = array_merge($prizes, $runningNumbers);
        $matchedPrizes = [];

        foreach ($allPrizes as $prize) {
            $prizeName = $this->nameMapping[$prize['name']] ?? $prize['name'];
            if (!isset($prize['number']) || !isset($prize['reward'])) continue;

            foreach ((array)$prize['number'] as $winningNumber) {
                if (preg_match('/^x+$/', $winningNumber)) continue;
                $winningLength = strlen($winningNumber);

                // Exact match
                if ($lotteryNumber === $winningNumber) {
                    $matchedPrizes[] = [
                        'prize_name' => $prizeName,
                        'winning_number' => $winningNumber,
                        'reward' => (int) $prize['reward'],
                        'match_type' => 'exact',
                    ];
                }

                if ($numberLength === 6) {
                    // Back 3
                    if ($prizeName === 'Back Three Digits' && $winningLength === 3 && substr($lotteryNumber, -3) === $winningNumber) {
                        $matchedPrizes[] = ['prize_name' => $prizeName, 'winning_number' => $winningNumber, 'reward' => (int) $prize['reward'], 'match_type' => 'last_3_digits'];
                    }
                    // Front 3
                    if ($prizeName === 'Front Three Digits' && $winningLength === 3 && substr($lotteryNumber, 0, 3) === $winningNumber) {
                        $matchedPrizes[] = ['prize_name' => $prizeName, 'winning_number' => $winningNumber, 'reward' => (int) $prize['reward'], 'match_type' => 'first_3_digits'];
                    }
                    // Back 2
                    if ($prizeName === 'Back Two Digits' && $winningLength === 2 && substr($lotteryNumber, -2) === $winningNumber) {
                        $matchedPrizes[] = ['prize_name' => $prizeName, 'winning_number' => $winningNumber, 'reward' => (int) $prize['reward'], 'match_type' => 'last_2_digits'];
                    }
                }
            }
        }
        return $matchedPrizes;
    }

    private function formatDrawResult($result)
    {
        return [
            'id' => $result->id,
            'draw_date' => Carbon::parse($result->draw_date)->format('Y-m-d'), // Format as YYYY-MM-DD only
            'date_th' => $result->date_th,
            'date_en' => $result->date_en,
            'prizes' => $result->prizes ?? [],
            'running_numbers' => $result->running_numbers ?? [],
        ];
    }
}