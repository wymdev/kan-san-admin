<?php

namespace App\Services;

use Illuminate\Database\Connection;
use RuntimeException;

/** Copies raw rows so passwords, tokens, timestamps and IDs are never re-created by models. */
class PostgresDataCopy
{
    public function run(Connection $source, Connection $target, bool $execute = false): array
    {
        if (! in_array($source->getDriverName(), ['mysql', 'mariadb', 'sqlite'], true) || $target->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Use a MySQL/MariaDB/SQLite source and a separate PostgreSQL target.');
        }
        if ($source->getDriverName() !== 'sqlite') {
            $source->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $source->statement('SET TRANSACTION READ ONLY');
        }
        $source->beginTransaction();
        try {
            return $target->transaction(function () use ($source, $target, $execute) {
                $schema = $target->getSchemaBuilder();
                $sourceSchema = $source->getDriverName() === 'sqlite' ? 'main' : $source->getDatabaseName();
                $tables = array_values(array_diff($source->getSchemaBuilder()->getTableListing($sourceSchema, schemaQualified: false), ['migrations']));
                sort($tables);
                $plans = [];
                foreach ($tables as $table) {
                    if (! $schema->hasTable($table)) {
                        throw new RuntimeException("Target is missing table $table. Run migrations and reconcile schema drift first.");
                    }
                    $columns = collect($schema->getColumns($table))->keyBy('name');
                    $sourceColumns = $source->getSchemaBuilder()->getColumnListing($table);
                    if (array_diff($sourceColumns, $columns->keys()->all())) {
                        throw new RuntimeException("Target is missing source columns in $table. No data was copied.");
                    }
                    $writable = $columns->filter(fn ($column) => empty($column['generation']))->keys()->all();
                    $copyColumns = array_values(array_intersect($sourceColumns, $writable));
                    foreach ($columns as $name => $column) {
                        if (in_array($name, $writable, true) && ! in_array($name, $sourceColumns, true)
                            && ! $column['nullable'] && $column['default'] === null && ! $column['auto_increment']) {
                            throw new RuntimeException("Required target column $table.$name is absent from source.");
                        }
                    }
                    $keys = collect($source->getSchemaBuilder()->getIndexes($table))->firstWhere('primary', true)['columns'] ?? [];
                    if (! $keys) {
                        throw new RuntimeException("Table $table needs a primary key for deterministic verification.");
                    }
                    if ($execute) {
                        $target->statement('LOCK TABLE '.$target->getQueryGrammar()->wrapTable($table).' IN ACCESS EXCLUSIVE MODE');
                    }
                    if ($target->table($table)->exists()) {
                        throw new RuntimeException("Target table $table is not empty. Existing target data will never be overwritten.");
                    }
                    $plans[$table] = compact('columns', 'copyColumns', 'keys');
                }
                $ordered = $this->orderTables($tables, $target);
                $report = [];
                foreach ($ordered as $table) {
                    ['columns' => $columns, 'copyColumns' => $copyColumns, 'keys' => $keys] = $plans[$table];
                    $count = $source->table($table)->count();
                    if ($execute) {
                        $sourceQuery = $source->table($table)->select($copyColumns);
                        foreach ($keys as $key) {
                            $sourceQuery->orderBy($key);
                        }
                        $sourceQuery->chunk(250, function ($rows) use ($target, $table, $columns) {
                            $data = $rows->map(function ($row) use ($columns, $table) {
                                $values = (array) $row;
                                foreach ($values as $name => &$value) {
                                    if ($value !== null && $columns[$name]['type_name'] === 'bool') {
                                        $value = (bool) $value;
                                    }
                                    if (is_string($value) && str_contains($value, "\0")) {
                                        // Laravel's PostgreSQL cache store base64-encodes serialized objects
                                        // containing NUL bytes, which PostgreSQL text cannot represent.
                                        if ($table === 'cache' && $name === 'value') {
                                            $value = base64_encode($value);
                                        } else {
                                            throw new RuntimeException("Unsupported NUL byte in $table.$name. Copy rolled back.");
                                        }
                                    }
                                }
                                return $values;
                            })->all();
                            $target->table($table)->insert($data);
                        });
                        // Compare every source column, including recomputed generated columns.
                        $verifyColumns = $source->getSchemaBuilder()->getColumnListing($table);
                        if ($count !== $target->table($table)->count()
                            || $this->digest($source, $table, $verifyColumns, $keys, $columns->all()) !== $this->digest($target, $table, $verifyColumns, $keys, $columns->all())) {
                            throw new RuntimeException("Row verification failed for $table. Copy rolled back.");
                        }
                    }
                    $report[] = [$table, $count, $execute ? 'Verified' : 'Ready'];
                }
                if ($execute) {
                    foreach ($plans as $table => $plan) {
                        foreach ($plan['columns'] as $name => $column) {
                            if ($column['auto_increment']) {
                                $sequence = $target->selectOne('SELECT pg_get_serial_sequence(?, ?) AS name', [$table, $name])->name;
                                if ($sequence) {
                                    $max = $target->table($table)->max($name);
                                    $target->select('SELECT setval(CAST(? AS regclass), ?, ?)', [$sequence, $max ?? 1, $max !== null]);
                                }
                            }
                        }
                    }
                }
                return $report;
            });
        } finally {
            $source->rollBack();
        }
    }

    private function orderTables(array $tables, Connection $target): array
    {
        $dependencies = [];
        foreach ($tables as $table) {
            $dependencies[$table] = array_values(array_intersect($tables, array_column($target->getSchemaBuilder()->getForeignKeys($table), 'foreign_table')));
        }
        $ordered = [];
        while ($dependencies) {
            $ready = array_keys(array_filter($dependencies, fn ($parents) => ! array_diff($parents, $ordered)));
            if (! $ready) {
                throw new RuntimeException('Cyclic foreign keys require a reviewed migration plan. Copy was not committed.');
            }
            foreach ($ready as $table) {
                $ordered[] = $table;
                unset($dependencies[$table]);
            }
        }
        return $ordered;
    }

    private function digest(Connection $connection, string $table, array $names, array $keys, array $columns): string
    {
        $rowHashes = [];
        $query = $connection->table($table)->select($names);
        foreach ($keys as $key) {
            $query->orderBy($key);
        }
        foreach ($query->cursor() as $row) {
            $values = [];
            foreach ($names as $name) {
                $value = $row->$name;
                $type = $columns[$name]['type_name'];
                if ($value !== null) {
                    if ($table === 'cache' && $name === 'value'
                        && in_array($connection->getDriverName(), ['pgsql', 'sqlite'], true)
                        && ! str_contains($value, ':') && ! str_contains($value, ';')) {
                        // Compare the original serialized bytes without instantiating cached objects.
                        $value = base64_decode($value, true);
                        if ($value === false) {
                            throw new RuntimeException('Invalid encoded cache value. Copy rolled back.');
                        }
                    }
                    $value = match ($type) {
                        'bool' => (bool) $value,
                        'uuid' => strlen($value) === 16 ? bin2hex($value) : strtolower(str_replace('-', '', $value)),
                        'json', 'jsonb' => $this->canonicalJson(json_decode($value, true, 512, JSON_THROW_ON_ERROR)),
                        'numeric', 'decimal' => str_contains((string) $value, '.') ? rtrim(rtrim((string) $value, '0'), '.') : (string) $value,
                        'timestamp', 'timestamptz' => preg_replace('/\.0+$/', '', (string) $value),
                        default => (string) $value,
                    };
                }
                $values[$name] = $value;
            }
            $rowHashes[] = hash('sha256', json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        }
        // MySQL and PostgreSQL collations can sort the same primary keys differently.
        // Sort fixed-length row hashes, preserving duplicates, instead of trusting database order.
        sort($rowHashes, SORT_STRING);
        $hash = hash_init('sha256');
        foreach ($rowHashes as $rowHash) {
            hash_update($hash, $rowHash);
        }
        return hash_final($hash);
    }

    private function canonicalJson(mixed $value): mixed
    {
        if (is_array($value)) {
            if (! array_is_list($value)) {
                ksort($value);
            }
            return array_map($this->canonicalJson(...), $value);
        }
        return $value;
    }
}
