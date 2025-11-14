<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Extract response_status from metadata JSON for faster queries
            $table->integer('response_status')
                ->nullable()
                ->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.response_status'))")
                ->after('metadata');
            
            // Extract duration_ms from metadata JSON
            $table->decimal('duration_ms', 10, 2)
                ->nullable()
                ->storedAs("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.duration_ms'))")
                ->after('response_status');
            
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
