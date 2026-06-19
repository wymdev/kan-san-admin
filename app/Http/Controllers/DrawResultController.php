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

    /**
     * Fetch latest lottery data from the official GLO API
     * @return array|null Returns mapped data or null on failure
     */
    private function fetchFromGlo()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://www.glo.or.th/api/lottery/getLatestLottery');

            if (!$response->ok()) {
                \Log::warning('GLO API returned non-OK status', ['status' => $response->status()]);
                return null;
            }

            $body = $response->json();
            if (empty($body)) {
                \Log::warning('GLO API returned empty response');
                return null;
            }

            return $this->mapGloApiData($body);
        } catch (\Exception $e) {
            \Log::warning('GLO API request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch lottery data for a specific date from the official GLO API
     * @param string $day Day (e.g., "01", "16")
     * @param string $month Month (e.g., "06")
     * @param string $year Year (e.g., "2026")
     * @return array|null Returns mapped data or null on failure
     */
    private function fetchFromGloByDate($day, $month, $year)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(20)->post('https://www.glo.or.th/api/checking/getLotteryResult', [
                'date' => $day,
                'month' => $month,
                'year' => $year,
            ]);

            if (!$response->ok()) {
                return null;
            }

            $body = $response->json();
            if (empty($body)) {
                return null;
            }

            return $this->mapGloApiData($body);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch latest lottery data from RapidAPI (fallback)
     * @return array|null Returns mapped data or null on failure
     */
    private function fetchFromRapidApi()
    {
        $apiKey = config('services.rapidapi.key', env('RAPIDAPI_KEY', ''));
        $host = config('services.rapidapi.host', env('RAPIDAPI_HOST', 'thai-lottery3.p.rapidapi.com'));

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => $host,
                'x-rapidapi-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get('https://thai-lottery3.p.rapidapi.com/api/v1/latest');

            if (!$response->ok()) {
                \Log::warning('RapidAPI returned non-OK status', ['status' => $response->status()]);
                return null;
            }

            $body = $response->json();
            if (!isset($body['success']) || !$body['success'] || !isset($body['data'])) {
                return null;
            }

            return $this->mapRapidApiData($body['data']);
        } catch (\Exception $e) {
            \Log::warning('RapidAPI request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch lottery data for a specific date from RapidAPI (fallback)
     * @return array|null
     */
    private function fetchFromRapidApiByDate($dateString)
    {
        $apiKey = config('services.rapidapi.key', env('RAPIDAPI_KEY', ''));
        $host = config('services.rapidapi.host', env('RAPIDAPI_HOST', 'thai-lottery3.p.rapidapi.com'));

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => $host,
                'x-rapidapi-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->get("https://thai-lottery3.p.rapidapi.com/api/v1/result/{$dateString}");

            if (!$response->ok()) {
                return null;
            }

            $body = $response->json();
            if (!isset($body['success']) || !$body['success'] || !isset($body['data'])) {
                return null;
            }

            return $this->mapRapidApiData($body['data']);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sync latest data - tries GLO API first, falls back to RapidAPI
     */
    public function syncLatest()
    {
        try {
            $source = 'GLO';
            $mapped = $this->fetchFromGlo();

            // Fallback to RapidAPI if GLO fails
            if (!$mapped) {
                $source = 'RapidAPI';
                $mapped = $this->fetchFromRapidApi();
            }

            if (!$mapped) {
                return back()->with('error', 'Failed to fetch data from both GLO and RapidAPI. Both sources are unavailable. Please try again later.');
            }

            if (!isset($mapped['draw_date']) || !$mapped['draw_date']) {
                return back()->with('error', 'Invalid draw date in API response.');
            }

            // Validate if prizes contain actual data (not placeholders)
            if (!$this->hasValidPrizeData($mapped['prizes'])) {
                return back()->with('warning', 'Lottery results not yet announced. Prizes data contains only placeholders.');
            }

            $drawDate = \Carbon\Carbon::parse($mapped['draw_date']);
            $dateString = $drawDate->format('Y-m-d');

            // Check if record exists and update, otherwise create
            $existingResult = DrawResult::whereDate('draw_date', $drawDate)->first();

            $saveData = [
                'date_th' => $mapped['date_th'],
                'date_en' => $mapped['date_en'] ?? $this->formatToEnglishDate($dateString),
                'prizes' => $mapped['prizes'],
                'running_numbers' => $mapped['running_numbers'],
                'endpoint' => $mapped['endpoint'] ?? "GLO API ({$source})",
            ];

            if ($existingResult) {
                $existingResult->update($saveData);
            } else {
                DrawResult::create(array_merge(['draw_date' => $drawDate], $saveData));
            }

            return back()->with('success', "Latest lottery result synced successfully via {$source}.");
        } catch (\Exception $e) {
            \Log::error('Sync latest lottery failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Sync all history - tries GLO API first per date, falls back to RapidAPI
     */
    public function syncAll()
    {
        try {
            set_time_limit(300); // 5 minutes

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

                $parts = explode('-', $dateString);
                $day = $parts[2];
                $month = $parts[1];
                $year = $parts[0];

                // Try GLO first, then RapidAPI
                $mapped = $this->fetchFromGloByDate($day, $month, $year);
                if (!$mapped) {
                    $mapped = $this->fetchFromRapidApiByDate($dateString);
                }

                if (!$mapped) {
                    $failed++;
                    continue;
                }

                // Validate if prizes contain actual data
                if (!$this->hasValidPrizeData($mapped['prizes'])) {
                    $skipped++;
                    continue;
                }

                $saveData = [
                    'date_th' => $mapped['date_th'],
                    'date_en' => $mapped['date_en'] ?? $this->formatToEnglishDate($dateString),
                    'prizes' => $mapped['prizes'],
                    'running_numbers' => $mapped['running_numbers'],
                    'endpoint' => $mapped['endpoint'] ?? "GLO/RapidAPI",
                ];

                // Update or Create
                if ($existing) {
                    $existing->update($saveData);
                } else {
                    DrawResult::create(array_merge(['draw_date' => $drawDate], $saveData));
                }

                $imported++;

                // Small delay to be polite to the API
                usleep(300000); // 0.3s
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
            \Log::error('Sync all lottery failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Map official GLO API data format to application's standard DB format
     */
    private function mapGloApiData($gloData)
    {
        // GLO API returns data in its own format - we need to extract and map it
        // The response structure may vary; handle common patterns
        $data = $gloData['response'] ?? $gloData['data'] ?? $gloData;
        
        if (is_array($data) && isset($data[0])) {
            $data = $data[0]; // Take the first/latest result
        }

        // Extract draw date
        $drawDate = $data['date'] ?? $data['announceDate'] ?? $data['period_date'] ?? null;
        if (!$drawDate) {
            return null;
        }

        // Parse the draw date - GLO might return various formats
        try {
            $parsedDate = \Carbon\Carbon::parse($drawDate);
            $dateString = $parsedDate->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }

        $dateTh = $this->formatToThaiDate($dateString);
        $dateEn = $this->formatToEnglishDate($dateString);

        $prizes = [];
        $runningNumbers = [];

        // Map GLO prize categories to our standard format
        $gloCategories = [
            'prizeFirst' => ['id' => 'prizeFirst', 'name' => 'รางวัลที่ 1', 'reward' => 6000000],
            'first' => ['id' => 'prizeFirst', 'name' => 'รางวัลที่ 1', 'reward' => 6000000],
            'prizeFirstNear' => ['id' => 'prizeFirstNear', 'name' => 'รางวัลข้างเคียงรางวัลที่ 1', 'reward' => 100000],
            'firstNear' => ['id' => 'prizeFirstNear', 'name' => 'รางวัลข้างเคียงรางวัลที่ 1', 'reward' => 100000],
            'near1' => ['id' => 'prizeFirstNear', 'name' => 'รางวัลข้างเคียงรางวัลที่ 1', 'reward' => 100000],
            'prizeSecond' => ['id' => 'prizeSecond', 'name' => 'รางวัลที่ 2', 'reward' => 200000],
            'second' => ['id' => 'prizeSecond', 'name' => 'รางวัลที่ 2', 'reward' => 200000],
            'prizeThird' => ['id' => 'prizeThird', 'name' => 'รางวัลที่ 3', 'reward' => 80000],
            'third' => ['id' => 'prizeThird', 'name' => 'รางวัลที่ 3', 'reward' => 80000],
            'prizeForth' => ['id' => 'prizeForth', 'name' => 'รางวัลที่ 4', 'reward' => 40000],
            'fourth' => ['id' => 'prizeForth', 'name' => 'รางวัลที่ 4', 'reward' => 40000],
            'prizeFifth' => ['id' => 'prizeFifth', 'name' => 'รางวัลที่ 5', 'reward' => 20000],
            'fifth' => ['id' => 'prizeFifth', 'name' => 'รางวัลที่ 5', 'reward' => 20000],
        ];

        $gloRunningCategories = [
            'runningNumberFrontThree' => ['id' => 'runningNumberFrontThree', 'name' => 'รางวัลเลขหน้า 3 ตัว', 'reward' => 4000],
            'frontThree' => ['id' => 'runningNumberFrontThree', 'name' => 'รางวัลเลขหน้า 3 ตัว', 'reward' => 4000],
            'last3f' => ['id' => 'runningNumberFrontThree', 'name' => 'รางวัลเลขหน้า 3 ตัว', 'reward' => 4000],
            'runningNumberBackThree' => ['id' => 'runningNumberBackThree', 'name' => 'รางวัลเลขท้าย 3 ตัว', 'reward' => 4000],
            'backThree' => ['id' => 'runningNumberBackThree', 'name' => 'รางวัลเลขท้าย 3 ตัว', 'reward' => 4000],
            'last3b' => ['id' => 'runningNumberBackThree', 'name' => 'รางวัลเลขท้าย 3 ตัว', 'reward' => 4000],
            'runningNumberBackTwo' => ['id' => 'runningNumberBackTwo', 'name' => 'รางวัลเลขท้าย 2 ตัว', 'reward' => 2000],
            'backTwo' => ['id' => 'runningNumberBackTwo', 'name' => 'รางวัลเลขท้าย 2 ตัว', 'reward' => 2000],
            'last2' => ['id' => 'runningNumberBackTwo', 'name' => 'รางวัลเลขท้าย 2 ตัว', 'reward' => 2000],
        ];

        // Try to extract prizes from various GLO response formats
        $prizeData = $data['prizes'] ?? $data['data'] ?? $data;
        
        if (is_array($prizeData)) {
            foreach ($prizeData as $key => $value) {
                // Determine numbers array
                $rawNumbers = [];
                if (is_array($value)) {
                    // Check for nested number array (e.g. "number" => [["value" => "123"], ...])
                    $numberList = $value['number'] ?? $value['numbers'] ?? [];
                    if (is_array($numberList)) {
                        foreach ($numberList as $numItem) {
                            if (is_array($numItem) && isset($numItem['value'])) {
                                $rawNumbers[] = (string) $numItem['value'];
                            } elseif (is_string($numItem) || is_numeric($numItem)) {
                                $rawNumbers[] = (string) $numItem;
                            }
                        }
                    }
                    // Fallback to check if it's a flat array of values (legacy formats)
                    if (empty($rawNumbers)) {
                        foreach ($value as $k => $v) {
                            if (is_numeric($k) && (is_string($v) || is_numeric($v))) {
                                $rawNumbers[] = (string) $v;
                            }
                        }
                    }
                } elseif (is_string($value) || is_numeric($value)) {
                    $rawNumbers[] = (string) $value;
                }

                $rawNumbers = array_filter($rawNumbers, fn($n) => strlen($n) > 0);
                if (empty($rawNumbers)) {
                    // Handle when prizes are in array format with 'id' and 'number' keys
                    if (is_array($value) && isset($value['id'])) {
                        $prizeId = $value['id'];
                        $numbers = $value['number'] ?? $value['numbers'] ?? [];
                        if (is_string($numbers)) {
                            $numbers = [$numbers];
                        }
                        
                        $extractedNumbers = [];
                        if (is_array($numbers)) {
                            foreach ($numbers as $numItem) {
                                if (is_array($numItem) && isset($numItem['value'])) {
                                    $extractedNumbers[] = (string) $numItem['value'];
                                } elseif (is_string($numItem) || is_numeric($numItem)) {
                                    $extractedNumbers[] = (string) $numItem;
                                }
                            }
                        }
                        $extractedNumbers = array_filter($extractedNumbers, fn($n) => strlen($n) > 0);

                        if (!empty($extractedNumbers)) {
                            if (isset($gloCategories[$prizeId])) {
                                $prizes[] = [
                                    'id' => $gloCategories[$prizeId]['id'],
                                    'name' => $gloCategories[$prizeId]['name'],
                                    'reward' => $value['reward'] ?? $gloCategories[$prizeId]['reward'],
                                    'amount' => count($extractedNumbers),
                                    'number' => array_values($extractedNumbers),
                                ];
                            } elseif (isset($gloRunningCategories[$prizeId])) {
                                $runningNumbers[] = [
                                    'id' => $gloRunningCategories[$prizeId]['id'],
                                    'name' => $gloRunningCategories[$prizeId]['name'],
                                    'reward' => $value['reward'] ?? $gloRunningCategories[$prizeId]['reward'],
                                    'amount' => count($extractedNumbers),
                                    'number' => array_values($extractedNumbers),
                                ];
                            }
                        }
                    }
                    continue;
                }

                // Check mapping
                if (isset($gloCategories[$key])) {
                    $prizes[] = [
                        'id' => $gloCategories[$key]['id'],
                        'name' => $gloCategories[$key]['name'],
                        'reward' => isset($value['price']) ? intval($value['price']) : $gloCategories[$key]['reward'],
                        'amount' => count($rawNumbers),
                        'number' => array_values($rawNumbers),
                    ];
                } elseif (isset($gloRunningCategories[$key])) {
                    $runningNumbers[] = [
                        'id' => $gloRunningCategories[$key]['id'],
                        'name' => $gloRunningCategories[$key]['name'],
                        'reward' => isset($value['price']) ? intval($value['price']) : $gloRunningCategories[$key]['reward'],
                        'amount' => count($rawNumbers),
                        'number' => array_values($rawNumbers),
                    ];
                }
            }
        }

        if (empty($prizes) && empty($runningNumbers)) {
            return null;
        }

        return [
            'draw_date' => $dateString,
            'date_th' => $dateTh,
            'date_en' => $dateEn,
            'prizes' => $prizes,
            'running_numbers' => $runningNumbers,
            'endpoint' => 'https://www.glo.or.th/api/lottery/getLatestLottery',
        ];
    }

    /**
     * Map RapidAPI data format to application's standard DB format
     */
    private function mapRapidApiData($apiData)
    {
        $resultDate = $apiData['resultDate'] ?? '';
        $results = $apiData['results'] ?? [];

        $dateTh = $this->formatToThaiDate($resultDate);
        $dateEn = $this->formatToEnglishDate($resultDate);

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
            'draw_date' => $resultDate,
            'date_th' => $dateTh,
            'date_en' => $dateEn,
            'prizes' => $prizes,
            'running_numbers' => $runningNumbers,
            'endpoint' => 'https://thai-lottery3.p.rapidapi.com (RapidAPI)',
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
