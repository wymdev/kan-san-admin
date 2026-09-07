<?php

namespace App\Console\Commands;

use App\Services\PostgresDataCopy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CopyDatabaseToPostgres extends Command
{
    protected $signature = 'database:copy-to-postgres {--source=mysql} {--target=pgsql_migration} {--execute : Copy into an empty, migrated PostgreSQL target}';

    protected $description = 'Inspect a database copy, or copy and verify all rows without modifying the source';

    public function handle(PostgresDataCopy $copy): int
    {
        try {
            $report = $copy->run(
                DB::connection($this->option('source')),
                DB::connection($this->option('target')),
                (bool) $this->option('execute'),
            );
            $this->table(['Table', 'Source rows', 'Result'], $report);
            $this->info($this->option('execute')
                ? 'Copy committed. Every row verified; IDs and sequences preserved. Source and app connection unchanged.'
                : 'Preflight passed. No rows copied. Stop all source writers before using --execute.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            // Query exceptions can contain customer data and credentials. Keep console output private-data free.
            $this->error($exception instanceof \RuntimeException && ! $exception instanceof \PDOException
                ? $exception->getMessage()
                : 'Copy failed; target transaction rolled back. Check database connectivity and schema compatibility.');
            return self::FAILURE;
        }
    }
}
