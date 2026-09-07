<?php

namespace App\Console\Commands;

use App\Services\PublicLotteryResultsService;
use Illuminate\Console\Command;
use Throwable;

class SyncLotteryResults extends Command
{
    protected $signature = 'lottery:sync-results {--history : Import up to 20 historical draws once}';

    protected $description = 'Fetch the latest Thai government draw, or perform the one-time history import';

    public function handle(PublicLotteryResultsService $service): int
    {
        try {
            $count = $this->option('history') ? $service->importHistoryOnce() : $service->syncLatest();
            $this->info($count ? "Synced $count draw result(s)." : 'History was already imported. No API request was made.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Results sync failed. Existing results were preserved; check API availability and database migrations.');
            return self::FAILURE;
        }
    }
}
