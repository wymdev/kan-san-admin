<?php

namespace App\Http\Controllers;

use App\Models\DrawResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DrawResultController extends Controller
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

    public function index(Request $request)
    {
        $query = DrawResult::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('date_th', 'like', '%' . $request->search . '%')
                    ->orWhere('date_en', 'like', '%' . $request->search . '%');
            });
        }

        // Draw date filter
        if ($request->filled('draw_date')) {
            $query->whereDate('draw_date', $request->draw_date);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('draw_date', $request->year);
        }

        // Month filter
        if ($request->filled('month')) {
            $query->whereMonth('draw_date', $request->month);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'draw_date');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $results = $query->paginate(15);

        // Get available years for filter
        $years = DrawResult::selectRaw('YEAR(draw_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('draw_results.index', compact('results', 'years'));
    }

    // Helper to ensure data is array (handles both model cast and legacy string data)
    private function ensureArray($data)
    {
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }
        return is_array($data) ? $data : [];
    }

    // Show detail page
    public function showDetail($id)
    {
        $result = DrawResult::findOrFail($id);

        // Process and sort prizes
        $prizesData = $this->ensureArray($result->prizes);
        $prizes = array_map(function ($p) {
            $p['name'] = $this->nameMapping[$p['name']] ?? $p['name'];
            $p['order'] = $this->prizeOrder[$p['name']] ?? 999;
            return $p;
        }, $prizesData);

        usort($prizes, fn($a, $b) => $a['order'] <=> $b['order']);

        // Process running numbers
        $runningData = $this->ensureArray($result->running_numbers);
        $running_numbers = array_map(function ($r) {
            $r['name'] = $this->nameMapping[$r['name']] ?? $r['name'];
            return $r;
        }, $runningData);

        return view('draw_results.show', compact('result', 'prizes', 'running_numbers'));
    }

    // Details for modal: AJAX JSON
    public function show($id)
    {
        $result = DrawResult::findOrFail($id);

        // Process and sort prizes
        $prizesData = $this->ensureArray($result->prizes);
        $prizes = array_map(function ($p) {
            $p['name'] = $this->nameMapping[$p['name']] ?? $p['name'];
            $p['order'] = $this->prizeOrder[$p['name']] ?? 999;
            return $p;
        }, $prizesData);

        usort($prizes, fn($a, $b) => $a['order'] <=> $b['order']);

        // Process running numbers
        $runningData = $this->ensureArray($result->running_numbers);
        $running_numbers = array_map(function ($r) {
            $r['name'] = $this->nameMapping[$r['name']] ?? $r['name'];
            return $r;
        }, $runningData);

        return response()->json([
            'date_en' => $result->date_en,
            'date_th' => $result->date_th,
            'endpoint' => $result->endpoint,
            'prizes' => $prizes,
            'running_numbers' => $running_numbers,
        ]);
    }

    /**
     * Validate if prize data contains actual winning numbers (not placeholders)
     */
    private function hasValidPrizeData($prizes)
    {
        if (empty($prizes) || !is_array($prizes)) {
            return false;
        }

        // Check if at least one prize has valid numbers (not all xxx or xxxxxx)
        foreach ($prizes as $prize) {
            if (isset($prize['number']) && is_array($prize['number'])) {
                foreach ($prize['number'] as $number) {
                    // If we find at least one number that's not a placeholder, it's valid
                    if (!preg_match('/^x+$/i', $number)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // Sync latest data (only if prizes have valid data)
    public function syncLatest()
    {
        try {
            $apiKey = config('services.rapidapi.key', env('RAPIDAPI_KEY', ''));
            $host = config('services.rapidapi.host', env('RAPIDAPI_HOST', 'thai-lottery3.p.rapidapi.com'));

            if (empty($apiKey)) {
                return back()->with('error', 'RapidAPI key is not configured.');
            }

            $response = Http::withHeaders([
                'x-rapidapi-host' => $host,
                'x-rapidapi-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get('https://thai-lottery3.p.rapidapi.com/api/v1/latest');

            if (!$response->ok()) {
                return back()->with('error', 'Failed to fetch latest data from RapidAPI. Error: ' . $response->status());
            }

            $body = $response->json();
            if (!isset($body['success']) || !$body['success'] || !isset($body['data'])) {
                return back()->with('error', 'Invalid or unsuccessful response from RapidAPI.');
            }

            $apiData = $body['data'];
            $resultDate = $apiData['resultDate'] ?? null;

            if (!$resultDate) {
                return back()->with('error', 'Invalid draw date in API response.');
            }

            $mapped = $this->mapRapidApiData($apiData);
            
            // Validate if prizes contain actual data (not placeholders)
            if (!$this->hasValidPrizeData($mapped['prizes'])) {
                return back()->with('warning', 'Lottery results not yet announced. Prizes data contains only placeholders.');
            }

            $drawDate = \Carbon\Carbon::parse($resultDate);

            // Check if record exists and update, otherwise create
            $existingResult = DrawResult::whereDate('draw_date', $drawDate)->first();

            if ($existingResult) {
                $existingResult->update([
                    'date_th' => $mapped['date_th'],
                    'date_en' => $this->formatToEnglishDate($resultDate),
                    'prizes' => $mapped['prizes'],
                    'running_numbers' => $mapped['running_numbers'],
                    'endpoint' => 'https://thai-lottery3.p.rapidapi.com/api/v1/latest',
                ]);
            } else {
                DrawResult::create([
                    'draw_date' => $drawDate,
                    'date_th' => $mapped['date_th'],
                    'date_en' => $this->formatToEnglishDate($resultDate),
                    'prizes' => $mapped['prizes'],
                    'running_numbers' => $mapped['running_numbers'],
                    'endpoint' => 'https://thai-lottery3.p.rapidapi.com/api/v1/latest',
                ]);
            }

            return back()->with('success', 'Latest lottery result synced successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Sync all history from API (only results with valid prize data)
    public function syncAll()
    {
        try {
            set_time_limit(300); // 5 minutes

            $apiKey = config('services.rapidapi.key', env('RAPIDAPI_KEY', ''));
            $host = config('services.rapidapi.host', env('RAPIDAPI_HOST', 'thai-lottery3.p.rapidapi.com'));

            if (empty($apiKey)) {
                return back()->with('error', 'RapidAPI key is not configured.');
            }

            // Generate expected draw dates for the last 3 years (e.g., 2024 to current year)
            $currentYear = intval(date('Y'));
            $drawDates = $this->generateDrawDates(2024, $currentYear);

            $imported = 0;
            $skipped = 0;
            $failed = 0;
            $alreadyExists = 0;

            foreach ($drawDates as $dateString) {
                // Parse date
                $drawDate = \Carbon\Carbon::parse($dateString);

                // Skip if date is in the future
                if ($drawDate->gt(now())) {
                    continue;
                }

                // Check if we already have this draw result
                $existing = DrawResult::whereDate('draw_date', $drawDate)->first();

                // If draw is older than 1 month and already exists with valid data, skip API call
                $isRecent = $drawDate->gt(now()->subMonth());
                if (!$isRecent && $existing && $this->hasValidPrizeData($this->ensureArray($existing->prizes))) {
                    $alreadyExists++;
                    continue;
                }

                // Fetch from RapidAPI
                $response = Http::withHeaders([
                    'x-rapidapi-host' => $host,
                    'x-rapidapi-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(20)->get("https://thai-lottery3.p.rapidapi.com/api/v1/result/{$dateString}");

                if (!$response->ok()) {
                    $failed++;
                    continue;
                }

                $body = $response->json();
                if (!isset($body['success']) || !$body['success'] || !isset($body['data'])) {
                    $failed++;
                    continue;
                }

                $apiData = $body['data'];
                $mapped = $this->mapRapidApiData($apiData);

                // Validate if prizes contain actual data
                if (!$this->hasValidPrizeData($mapped['prizes'])) {
                    $skipped++;
                    continue;
                }

                // Update or Create
                if ($existing) {
                    $existing->update([
                        'date_th' => $mapped['date_th'],
                        'date_en' => $this->formatToEnglishDate($dateString),
                        'prizes' => $mapped['prizes'],
                        'running_numbers' => $mapped['running_numbers'],
                        'endpoint' => "https://thai-lottery3.p.rapidapi.com/api/v1/result/{$dateString}",
                    ]);
                } else {
                    DrawResult::create([
                        'draw_date' => $drawDate,
                        'date_th' => $mapped['date_th'],
                        'date_en' => $this->formatToEnglishDate($dateString),
                        'prizes' => $mapped['prizes'],
                        'running_numbers' => $mapped['running_numbers'],
                        'endpoint' => "https://thai-lottery3.p.rapidapi.com/api/v1/result/{$dateString}",
                    ]);
                }

                $imported++;

                // Small delay to be polite to the API
                usleep(150000); // 0.15s
            }

            $message = "Sync Complete: {$imported} imported/updated.";

            if ($alreadyExists > 0) {
                $message .= " | {$alreadyExists} up-to-date (skipped API call).";
            }
            if ($skipped > 0) {
                $message .= " | {$skipped} skipped (invalid data).";
            }
            if ($failed > 0) {
                $message .= " | {$failed} failed/not found.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Map RapidAPI data format to application's standard DB format
     */
    private function mapRapidApiData($apiData)
    {
        $resultDate = $apiData['resultDate'] ?? '';
        $results = $apiData['results'] ?? [];

        $dateTh = $this->formatToThaiDate($resultDate);

        $prizes = [];
        $runningNumbers = [];

        $prizeMapping = [
            'first' => ['id' => 'prizeFirst', 'name' => 'รางวัลที่ 1', 'reward' => 6000000],
            'near1' => ['id' => 'prizeFirstNear', 'name' => 'รางวัลข้างเคียงรางวัลที่ 1', 'reward' => 100000],
            'second' => ['id' => 'prizeSecond', 'name' => 'รางวัลที่ 2', 'reward' => 200000],
            'third' => ['id' => 'prizeThird', 'name' => 'รางวัลที่ 3', 'reward' => 80000],
            'fourth' => ['id' => 'prizeForth', 'name' => 'รางวัลที่ 4', 'reward' => 40000],
            'fifth' => ['id' => 'prizeFifth', 'name' => 'รางวัลที่ 5', 'reward' => 20000],
        ];

        $runningMapping = [
            'last3f' => ['id' => 'runningNumberFrontThree', 'name' => 'รางวัลเลขหน้า 3 ตัว', 'reward' => 4000],
            'last3b' => ['id' => 'runningNumberBackThree', 'name' => 'รางวัลเลขท้าย 3 ตัว', 'reward' => 4000],
            'last2' => ['id' => 'runningNumberBackTwo', 'name' => 'รางวัลเลขท้าย 2 ตัว', 'reward' => 2000],
        ];

        foreach ($results as $item) {
            $fieldName = $item['fieldName'] ?? '';
            $numbers = isset($item['numbers']) ? array_column($item['numbers'], 'value') : [];

            if (isset($prizeMapping[$fieldName])) {
                $prizes[] = [
                    'id' => $prizeMapping[$fieldName]['id'],
                    'name' => $prizeMapping[$fieldName]['name'],
                    'reward' => $item['price'] ?? $prizeMapping[$fieldName]['reward'],
                    'amount' => count($numbers),
                    'number' => $numbers,
                ];
            } elseif (isset($runningMapping[$fieldName])) {
                $runningNumbers[] = [
                    'id' => $runningMapping[$fieldName]['id'],
                    'name' => $runningMapping[$fieldName]['name'],
                    'reward' => $item['price'] ?? $runningMapping[$fieldName]['reward'],
                    'amount' => count($numbers),
                    'number' => $numbers,
                ];
            }
        }

        return [
            'date_th' => $dateTh,
            'prizes' => $prizes,
            'running_numbers' => $runningNumbers,
        ];
    }

    /**
     * Format standard date YYYY-MM-DD to Thai display format
     */
    private function formatToThaiDate($ymdDate)
    {
        $monthsTh = [
            "01" => "มกราคม",
            "02" => "กุมภาพันธ์",
            "03" => "มีนาคม",
            "04" => "เมษายน",
            "05" => "พฤษภาคม",
            "06" => "มิถุนายน",
            "07" => "กรกฎาคม",
            "08" => "สิงหาคม",
            "09" => "กันยายน",
            "10" => "ตุลาคม",
            "11" => "พฤศจิกายน",
            "12" => "ธันวาคม"
        ];

        $parts = explode('-', $ymdDate);
        if (count($parts) !== 3) {
            return $ymdDate;
        }

        $year = intval($parts[0]) + 543;
        $month = $monthsTh[$parts[1]] ?? '';
        $day = intval($parts[2]);

        return "{$day} {$month} {$year}";
    }

    /**
     * Format standard date YYYY-MM-DD to English display format
     */
    private function formatToEnglishDate($ymdDate)
    {
        $monthsEn = [
            "01" => "January",
            "02" => "February",
            "03" => "March",
            "04" => "April",
            "05" => "May",
            "06" => "June",
            "07" => "July",
            "08" => "August",
            "09" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        ];

        $parts = explode('-', $ymdDate);
        if (count($parts) !== 3) {
            return $ymdDate;
        }

        $year = intval($parts[0]);
        $month = $monthsEn[$parts[1]] ?? '';
        $day = intval($parts[2]);

        return "{$day} {$month} {$year}";
    }

    /**
     * Generate expected Thai lottery draw dates for a given year range
     */
    private function generateDrawDates($startYear, $endYear)
    {
        $dates = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                // First draw of month
                if ($month == 1) {
                    // Handled as Dec 30th of previous year
                } elseif ($month == 5) {
                    $dates[] = sprintf("%04d-%02d-02", $year, $month); // May 2 (Labor Day holiday)
                } else {
                    $dates[] = sprintf("%04d-%02d-01", $year, $month); // 1st of month
                }

                // Second draw of month
                if ($month == 1) {
                    $dates[] = sprintf("%04d-%02d-17", $year, $month); // Jan 17 (Teachers' Day holiday)
                } else {
                    $dates[] = sprintf("%04d-%02d-16", $year, $month); // 16th of month
                }
            }
            // Dec 30 draw of each year (New Year's Eve holiday)
            $dates[] = sprintf("%04d-12-30", $year);
        }
        
        rsort($dates);
        
        return $dates;
    }
}
