<?php

namespace Tests\Feature;

use App\Services\PostgresDataCopy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PostgresDataCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_copy_preserves_rows_and_resets_sequences_and_refuses_overwrite(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Run against the isolated PostgreSQL test database.');
        }
        config(['database.connections.copy_source' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $source = DB::connection('copy_source');
        $source->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        $source->table('users')->insert([
            'id' => 500, 'name' => 'ทดสอบ', 'email' => 'copy@example.test', 'password' => 'keep-the-exact-hash',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-02 00:00:00',
        ]);
        $copy = new PostgresDataCopy;
        $copy->run($source, DB::connection());
        $this->assertSame(0, DB::table('users')->count());
        $copy->run($source, DB::connection(), true);
        $this->assertSame('keep-the-exact-hash', DB::table('users')->where('id', 500)->value('password'));
        $this->assertSame('ทดสอบ', DB::table('users')->where('id', 500)->value('name'));
        $this->assertSame(1, $source->table('users')->count());
        $this->assertSame(501, DB::table('users')->insertGetId(['name' => 'Next', 'email' => 'next@example.test', 'password' => 'unused']));
        try {
            $copy->run($source, DB::connection(), true);
            $this->fail('A nonempty target must be refused.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('not empty', $exception->getMessage());
        } finally {
            DB::purge('copy_source');
        }
        $this->assertSame(2, DB::table('users')->count());
    }

    public function test_failed_copy_rolls_back_all_previously_inserted_rows(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Run against the isolated PostgreSQL test database.');
        }
        config(['database.connections.copy_failure' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $source = DB::connection('copy_failure');
        $source->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('password');
        });
        // SQLite intentionally has no unique email constraint here; PostgreSQL must reject it.
        $rows = [];
        for ($index = 0; $index <= 250; $index++) {
            $rows[] = ['id' => 100 + $index, 'name' => 'Test', 'email' => 'copy-'.($index === 250 ? 0 : $index).'@example.test', 'password' => 'unchanged'];
        }
        $source->table('users')->insert($rows);
        try {
            (new PostgresDataCopy)->run($source, DB::connection(), true);
            $this->fail('Invalid data must not be committed.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertSame(0, DB::table('users')->count());
            $this->assertSame(251, $source->table('users')->count());
        } finally {
            DB::purge('copy_failure');
        }
    }

    public function test_serialized_cache_objects_remain_readable_after_copy(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Run against the isolated PostgreSQL test database.');
        }
        config(['database.connections.copy_cache' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $source = DB::connection('copy_cache');
        $source->getSchemaBuilder()->create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });
        // Protected model attributes serialize with NUL bytes, as in the MySQL cache store.
        $model = new \App\Models\User(['name' => 'ทดสอบ']);
        $serialized = serialize($model);
        $this->assertStringContainsString("\0", $serialized);
        $source->table('cache')->insert([
            'key' => 'cached-model', 'value' => $serialized, 'expiration' => time() + 3600,
        ]);
        try {
            (new PostgresDataCopy)->run($source, DB::connection(), true);
            $store = new \Illuminate\Cache\DatabaseStore(DB::connection(), 'cache', '');
            $this->assertEquals($model, $store->get('cached-model'));
            $this->assertSame($serialized, $source->table('cache')->value('value'));
        } finally {
            DB::purge('copy_cache');
        }
    }
}
