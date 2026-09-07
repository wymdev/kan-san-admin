<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();
            $status = match ($driver) {
                'pgsql' => "NULLIF(metadata->>'response_status', '')::integer",
                'sqlite' => "json_extract(metadata, '$.response_status')",
                default => "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.response_status'))",
            };
            $duration = match ($driver) {
                'pgsql' => "NULLIF(metadata->>'duration_ms', '')::numeric",
                'sqlite' => "json_extract(metadata, '$.duration_ms')",
                default => "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.duration_ms'))",
            };
            $statusColumn = $table->integer('response_status')->nullable();
            $driver === 'pgsql' ? $statusColumn->storedAs($status) : $statusColumn->virtualAs($status);
            $durationColumn = $table->decimal('duration_ms', 10, 2)->nullable();
            $driver === 'sqlite' ? $durationColumn->virtualAs($duration) : $durationColumn->storedAs($duration);
            
            // Add indexes for these virtual columns
            $table->index('response_status');
            $table->index('duration_ms');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['response_status', 'duration_ms']);
        });
    }
};
