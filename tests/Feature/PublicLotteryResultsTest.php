<?php

namespace Tests\Feature;

use App\Models\DrawResult;
use App\Services\PublicLotteryResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class PublicLotteryResultsTest extends TestCase
{
    use RefreshDatabase;

    private function resultFixture(string $date = '2026-01-01'): array
    {
        $categories = [
            ['first-prize', 6, 1], ['near-first-prize', 6, 2], ['second-prize', 6, 5],
            ['third-prize', 6, 10], ['fourth-prize', 6, 50], ['fifth-prize', 6, 100],
            ['first-three-digit', 3, 2], ['last-three-digit', 3, 2], ['last-two-digit', 2, 1],
        ];
        return ['lottery' => 'thai-government', 'drawDate' => $date, 'prizes' => array_map(fn ($category) => [
            'slug' => $category[0], 'amount' => '2000',
            'numbers' => array_map(fn ($number) => str_pad((string) $number, $category[1], '0', STR_PAD_LEFT), range(1, $category[2])),
        ], $categories)];
    }

    public function test_latest_uses_limit_one_preserves_leading_zeros_and_existing_ids(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/api/public/results*' => Http::response(['success' => true, 'data' => [$this->resultFixture()]])]);
        $service = new PublicLotteryResultsService;
        $this->assertSame(1, $service->syncLatest());
        $id = DrawResult::first()->id;
        $this->assertSame(1, $service->syncLatest());
        $this->assertSame(1, DrawResult::count());
        $this->assertSame($id, DrawResult::first()->id);
        $this->assertSame(['000001'], DrawResult::first()->prizes[0]['number']);
        $this->assertSame(['01'], DrawResult::first()->running_numbers[2]['number']);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request['lottery'] === 'thai-government' && (int) $request['limit'] === 1
            && ! $request->hasHeader('x-rapidapi-key'));
        $this->assertFalse($service->historyImported());
        $this->get('/lottery/result/2026-01-01')->assertOk()->assertSee('000001');
    }

    public function test_history_fetches_once_and_persists_completion_across_service_instances(): void
    {
        Http::fake(['*/api/public/results*' => Http::response(['success' => true, 'data' => [$this->resultFixture(), $this->resultFixture('2025-12-16')]])]);
        $this->assertSame(2, (new PublicLotteryResultsService)->importHistoryOnce());
        $this->assertSame(0, (new PublicLotteryResultsService)->importHistoryOnce());
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => (int) $request['limit'] === 20);
        $this->assertTrue((new PublicLotteryResultsService)->historyImported());
        $this->assertSame(2, DrawResult::count());
    }

    public function test_incomplete_history_response_rolls_back_and_does_not_mark_completion(): void
    {
        $invalid = $this->resultFixture('2025-12-16');
        $invalid['prizes'][0]['numbers'] = ['xxxxxx'];
        Http::fake(['*/api/public/results*' => Http::response(['success' => true, 'data' => [$this->resultFixture(), $invalid]])]);
        try {
            (new PublicLotteryResultsService)->importHistoryOnce();
            $this->fail('Placeholder results must be rejected.');
        } catch (RuntimeException) {
            $this->assertSame(0, DrawResult::count());
            $this->assertFalse((new PublicLotteryResultsService)->historyImported());
        }
        Http::assertSentCount(1);
    }

    public function test_http_failure_preserves_existing_results_and_completion_state(): void
    {
        $service = new PublicLotteryResultsService;
        DrawResult::create($service->mapResult($this->resultFixture()));
        Http::fake(['*/api/public/results*' => Http::response([], 503)]);
        try {
            $service->importHistoryOnce();
            $this->fail('An HTTP failure must not be accepted.');
        } catch (\Illuminate\Http\Client\RequestException) {
            $this->assertSame(1, DrawResult::count());
            $this->assertFalse($service->historyImported());
        }
    }
}
