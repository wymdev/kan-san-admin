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
            $response = Http::timeout(30)->get('https://lotto.api.rayriffy.com/latest');

            if (!$response->ok()) {
                return back()->with('error', 'Failed to fetch data from API');
            }

            $data = $response->json('response');

            if (!$data || !isset($data['date'], $data['prizes'])) {
                return back()->with('error', 'Invalid data format from API');
            }

            // Validate if prizes contain actual data (not placeholders)
            if (!$this->hasValidPrizeData($data['prizes'])) {
                return back()->with('warning', 'Lottery results not yet announced. Prizes data contains only placeholders.');
            }

            $drawDate = $this->parseApiDate($data['date']);

            // Check if record exists and update, otherwise create
            $existingResult = DrawResult::whereDate('draw_date', $drawDate)->first();

            if ($existingResult) {
                $existingResult->update([
                    'date_th' => $data['date'],
                    'date_en' => $this->translateThaiDate($data['date']),
                    'prizes' => $data['prizes'],
                    'running_numbers' => $data['runningNumbers'] ?? $data['runningnumbers'] ?? [],
                    'endpoint' => $data['endpoint'] ?? null,
                ]);
            } else {
                DrawResult::create([
                    'draw_date' => $drawDate,
                    'date_th' => $data['date'],
                    'date_en' => $this->translateThaiDate($data['date']),
                    'prizes' => $data['prizes'],
                    'running_numbers' => $data['runningNumbers'] ?? $data['runningnumbers'] ?? [],
                    'endpoint' => $data['endpoint'] ?? null,
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

            // 1. Fetch the full list
            $listResponse = Http::timeout(30)->get('https://lotto.api.rayriffy.com/list/1');

            if (!$listResponse->ok()) {
                return back()->with('error', 'Failed to fetch list from API');
            }

            $list = $listResponse->json('response');

            if (!is_array($list)) {
                return back()->with('error', 'Invalid list format from API');
            }

            $imported = 0;
            $skipped = 0;
            $failed = 0;
            $alreadyExists = 0;

            foreach ($list as $entry) {
                try {
                    // Optimization: Check if we already have this valid result using the date from the list
                    // The list has "date" like "1 กุมภาพันธ์ 2569"
                    $parsedDate = $this->parseApiDate($entry['date']);
                    $existing = DrawResult::whereDate('draw_date', $parsedDate)->first();

                    // Logic: 
                    // 1. If result is recent (last 3 months), always fetch/update to catch corrections.
                    // 2. If result is old (> 3 months) AND we have a valid local copy, skip it to save time.
                    $isRecent = \Carbon\Carbon::parse($parsedDate)->gt(now()->subMonths(3));

                    if (!$isRecent && $existing && $this->hasValidPrizeData($this->ensureArray($existing->prizes))) {
                        $alreadyExists++;
                        continue;
                    }

                    // Fetch details (if recent OR missing/invalid)
                    $api = Http::timeout(20)->get('https://lotto.api.rayriffy.com/lotto/' . $entry['id']);

                    if (!$api->ok()) {
                        $failed++;
                        continue;
                    }

                    $res = $api->json('response');

                    if (!is_array($res) || !isset($res['date'], $res['prizes'])) {
                        $failed++;
                        continue;
                    }

                    // Skip if prizes don't have valid data
                    if (!$this->hasValidPrizeData($res['prizes'])) {
                        $skipped++;
                        continue;
                    }

                    $draw_date = $this->parseApiDate($res['date']);

                    // Check if record exists and update, otherwise create
                    if ($existing) {
                        $existing->update([
                            'date_th' => $res['date'],
                            'date_en' => $this->translateThaiDate($res['date']),
                            'prizes' => $res['prizes'],
                            'running_numbers' => $res['runningNumbers'] ?? $res['runningnumbers'] ?? [],
                            'endpoint' => $res['endpoint'] ?? null,
                        ]);
                    } else {
                        DrawResult::create([
                            'draw_date' => $draw_date,
                            'date_th' => $res['date'],
                            'date_en' => $this->translateThaiDate($res['date']),
                            'prizes' => $res['prizes'],
                            'running_numbers' => $res['runningNumbers'] ?? $res['runningnumbers'] ?? [],
                            'endpoint' => $res['endpoint'] ?? null,
                        ]);
                    }

                    $imported++;
                    // Small delay to be nice to the API
                    usleep(100000); // 0.1s

                } catch (\Exception $e) {
                    $failed++;
                }
            }

            $message = "Sync Complete: {$imported} imported/updated.";

            if ($alreadyExists > 0) {
                $message .= " | {$alreadyExists} up-to-date (skipped).";
            }
            if ($skipped > 0) {
                $message .= " | {$skipped} skipped (invalid data).";
            }
            if ($failed > 0) {
                $message .= " | {$failed} failed.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Utility: convert Thai date to Y-m-d
    private function parseApiDate($thaiDate)
    {
        $months = [
            "มกราคม" => "01",
            "กุมภาพันธ์" => "02",
            "มีนาคม" => "03",
            "เมษายน" => "04",
            "พฤษภาคม" => "05",
            "มิถุนายน" => "06",
            "กรกฎาคม" => "07",
            "สิงหาคม" => "08",
            "กันยายน" => "09",
            "ตุลาคม" => "10",
            "พฤศจิกายน" => "11",
            "ธันวาคม" => "12"
        ];
        preg_match('/(\d{1,2}) (\S+) (\d{4})/', $thaiDate, $m);
        if (count($m) !== 4)
            return $thaiDate;
        $y = intval($m[3]) > 2400 ? intval($m[3]) - 543 : intval($m[3]);
        return "$y-{$months[$m[2]]}-" . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }

    // Thai to English date, for display
    public function translateThaiDate($thaiDate)
    {
        $months = [
            "มกราคม" => "January",
            "กุมภาพันธ์" => "February",
            "มีนาคม" => "March",
            "เมษายน" => "April",
            "พฤษภาคม" => "May",
            "มิถุนายน" => "June",
            "กรกฎาคม" => "July",
            "สิงหาคม" => "August",
            "กันยายน" => "September",
            "ตุลาคม" => "October",
            "พฤศจิกายน" => "November",
            "ธันวาคม" => "December"
        ];
        $en = $thaiDate;
        foreach ($months as $th => $enMonth)
            $en = str_replace($th, $enMonth, $en);
        if (preg_match('/(\d{1,2}) (\w+) (\d{4})/', $en, $parts)) {
            $year = intval($parts[3]);
            if ($year > 2400)
                $year -= 543;
            $en = "{$parts[1]} {$parts[2]} {$year}";
        }
        return $en;
    }

}
