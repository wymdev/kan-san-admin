<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckDatabaseConnection extends Command
{
    protected $signature = 'database:check {--connection= : Connection name; defaults to the active app connection}';

    protected $description = 'Check database connectivity with a read-only SELECT';

    public function handle(): int
    {
        $name = $this->option('connection') ?: config('database.default');
        try {
            $connection = DB::connection($name);
            $connection->selectOne('SELECT 1 AS connected');
            $this->info("Connected successfully: $name ({$connection->getDriverName()}).");
            return self::SUCCESS;
        } catch (Throwable) {
            $this->error("Connection failed: $name. Check its host, port, database, credentials and TLS settings. No data was changed.");
            return self::FAILURE;
        }
    }
}
