<?php

namespace Tests\Feature;

use App\Support\DatabaseSql;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_activity_fields_preserve_numeric_values(): void
    {
        DB::table('activity_logs')->insert([
            'action' => 'test', 'description' => 'Compatibility test', 'context' => 'admin_portal',
            'metadata' => json_encode(['response_status' => 503, 'duration_ms' => 1000.75]),
            'created_at' => now(),
        ]);
        $row = DB::table('activity_logs')->where('duration_ms', '>', 1000)->first();
        $this->assertSame(503, (int) $row->response_status);
        $this->assertSame(1000.75, (float) $row->duration_ms);
    }

    public function test_reporting_periods_and_bound_date_distance(): void
    {
        DB::table('users')->insert([
            'name' => 'Test', 'email' => 'test@example.test', 'password' => 'unused',
            'created_at' => '2021-01-01 13:00:00',
        ]);
        $row = DB::table('users')->selectRaw(DatabaseSql::period('created_at', '%x Week %v').' AS period')
            ->selectRaw(DatabaseSql::part('created_at', 'hour').' AS hour')
            ->selectRaw(DatabaseSql::dateDistance('created_at').' AS distance', ['2021-01-03'])->first();
        $this->assertSame('2020 Week 53', $row->period);
        $this->assertSame(13, (int) $row->hour);
        $this->assertSame(2, (int) $row->distance);
    }
}
