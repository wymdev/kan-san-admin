<?php

namespace App\Services;

use App\Models\DrawResult;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PublicLotteryResultsService
{
    public const HISTORY_KEY = 'thai-government-results';

    private const PRIZES = [
        'first-prize' => ['prizeFirst', 'รางวัลที่ 1', 6, 1],
        'near-first-prize' => ['prizeFirstNear', 'รางวัลข้างเคียงรางวัลที่ 1', 6, 2],
        'second-prize' => ['prizeSecond', 'รางวัลที่ 2', 6, 5],
        'third-prize' => ['prizeThird', 'รางวัลที่ 3', 6, 10],
        'fourth-prize' => ['prizeForth', 'รางวัลที่ 4', 6, 50],
        'fifth-prize' => ['prizeFifth', 'รางวัลที่ 5', 6, 100],
        'first-three-digit' => ['runningNumberFrontThree', 'รางวัลเลขหน้า 3 ตัว', 3, 2],
        'last-three-digit' => ['runningNumberBackThree', 'รางวัลเลขท้าย 3 ตัว', 3, 2],
        'last-two-digit' => ['runningNumberBackTwo', 'รางวัลเลขท้าย 2 ตัว', 2, 1],
    ];

    public function historyImported(): bool
    {
        return DB::table('external_syncs')->where('key', self::HISTORY_KEY)->whereNotNull('history_imported_at')->exists();
    }

    public function syncLatest(): int
    {
        return $this->sync(false);
    }

    public function importHistoryOnce(): int
    {
        return $this->sync(true);
    }

    private function sync(bool $history): int
    {
        // Both operations lock the same row so latest/history cannot race or duplicate draws.
        $dates = DB::transaction(function () use ($history) {
            DB::table('external_syncs')->insertOrIgnore(['key' => self::HISTORY_KEY]);
            $state = DB::table('external_syncs')->where('key', self::HISTORY_KEY)->lockForUpdate()->first();
            if ($history && $state->history_imported_at !== null) {
                return [];
            }
            $limit = $history ? 20 : 1;
            // Deliberately one request; history is never scheduled or paginated.
            $response = Http::acceptJson()->connectTimeout(10)->timeout(30)
                ->get($this->endpoint(), ['lottery' => 'thai-government', 'limit' => $limit]);
            $response->throw();
            $payload = $response->json();
            if (! is_array($payload) || ($payload['success'] ?? false) !== true
                || ! is_array($payload['data'] ?? null) || ! array_is_list($payload['data'])
                || count($payload['data']) < 1 || count($payload['data']) > $limit) {
                throw new RuntimeException('The results API returned an invalid or empty response.');
            }
            $mapped = array_map($this->mapResult(...), $payload['data']);
            $dates = array_column($mapped, 'draw_date');
            if (count($dates) !== count(array_unique($dates))) {
                throw new RuntimeException('The results API returned duplicate draw dates.');
            }
            foreach ($mapped as $data) {
                // Existing IDs are retained so purchases keep their result relationships.
                $result = DrawResult::whereDate('draw_date', $data['draw_date'])->first();
                $result ? $result->update($data) : DrawResult::create($data);
            }
            if ($history) {
                DB::table('external_syncs')->where('key', self::HISTORY_KEY)->update(['history_imported_at' => now()]);
            }
            return $dates;
        });

        if ($dates) {
            Cache::forget('latest_draw_result');
            foreach ($dates as $date) {
                for ($offset = -LotteryResultCheckerService::MAX_POSTPONE_DAYS; $offset <= LotteryResultCheckerService::MAX_POSTPONE_DAYS; $offset++) {
                    Cache::forget('draw_result_date_'.CarbonImmutable::parse($date)->addDays($offset)->format('Y-m-d'));
                }
            }
        }
        return count($dates);
    }

    public function mapResult(array $result): array
    {
        if (($result['lottery'] ?? null) !== 'thai-government'
            || ! is_string($result['drawDate'] ?? null)
            || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $result['drawDate'])) {
            throw new RuntimeException('The results API returned an unexpected lottery or draw date.');
        }
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $result['drawDate']);
        if ($date->format('Y-m-d') !== $result['drawDate'] || $date->isFuture()) {
            throw new RuntimeException('The results API returned an invalid or future draw date.');
        }
        $prizes = [];
        $running = [];
        $seen = [];
        foreach ($result['prizes'] ?? [] as $prize) {
            $slug = $prize['slug'] ?? '';
            if (! isset(self::PRIZES[$slug]) || isset($seen[$slug])) {
                throw new RuntimeException('The results API returned an unknown or duplicate prize category.');
            }
            [$id, $name, $digits, $count] = self::PRIZES[$slug];
            $numbers = $prize['numbers'] ?? null;
            if (! is_array($numbers) || ! array_is_list($numbers) || count($numbers) !== $count
                || ! is_numeric($prize['amount'] ?? null) || (float) $prize['amount'] <= 0) {
                throw new RuntimeException('The results API returned incomplete prize data.');
            }
            foreach ($numbers as $number) {
                if (! is_string($number) || ! preg_match('/^[0-9]{'.$digits.'}$/', $number)) {
                    throw new RuntimeException('The results API returned invalid or placeholder winning numbers.');
                }
            }
            $mapped = ['id' => $id, 'name' => $name, 'reward' => (string) $prize['amount'], 'amount' => $count, 'number' => $numbers];
            if (str_starts_with($id, 'runningNumber')) {
                $running[] = $mapped;
            } else {
                $prizes[] = $mapped;
            }
            $seen[$slug] = true;
        }
        if (count($seen) !== count(self::PRIZES)) {
            throw new RuntimeException('The results API has not published all prize categories yet.');
        }
        return [
            'draw_date' => $date->format('Y-m-d'),
            'date_th' => $date->locale('th')->translatedFormat('j F').' '.($date->year + 543),
            'date_en' => $date->locale('en')->format('j F Y'),
            'prizes' => $prizes, 'running_numbers' => $running,
            'endpoint' => $this->endpoint(),
        ];
    }

    private function endpoint(): string
    {
        return rtrim(config('services.lottery_results.base_url'), '/').'/api/public/results';
    }
}
