<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Small, explicit SQL expressions for the supported relational databases. */
final class DatabaseSql
{
    public static function period(string $column, string $format): string
    {
        $column = self::column($column);
        $postgres = [
            '%Y-%m' => 'YYYY-MM', '%Y-%m-%d' => 'YYYY-MM-DD',
            '%Y-%m-%d %H:00' => 'YYYY-MM-DD HH24:00',
            '%x Week %v' => 'IYYY "Week" IW',
        ];
        if (! isset($postgres[$format])) {
            throw new InvalidArgumentException('Unsupported reporting period.');
        }

        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR($column, '{$postgres[$format]}')",
            'sqlite' => $format === '%x Week %v'
                ? "printf('%04d Week %02d', CAST(strftime('%Y', date($column, '-3 days', 'weekday 4')) AS INTEGER), CAST((CAST(strftime('%j', date($column, '-3 days', 'weekday 4')) AS INTEGER) - 1) / 7 + 1 AS INTEGER))"
                : "strftime('$format', $column)",
            default => "DATE_FORMAT($column, '$format')",
        };
    }

    public static function part(string $column, string $part): string
    {
        $column = self::column($column);
        $sqlite = ['year' => '%Y', 'month' => '%m', 'hour' => '%H', 'dow' => '%w'];
        $mysql = ['year' => 'YEAR', 'month' => 'MONTH', 'hour' => 'HOUR', 'dow' => 'DAYOFWEEK'];
        if (! isset($sqlite[$part])) {
            throw new InvalidArgumentException('Unsupported date part.');
        }
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "CAST(EXTRACT($part FROM CAST($column AS TIMESTAMP)) AS INTEGER)".($part === 'dow' ? ' + 1' : ''),
            'sqlite' => "CAST(strftime('{$sqlite[$part]}', $column) AS INTEGER)".($part === 'dow' ? ' + 1' : ''),
            default => "{$mysql[$part]}($column)",
        };
    }

    public static function age(string $column): string
    {
        $column = self::column($column);
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "CAST(EXTRACT(YEAR FROM AGE(CURRENT_DATE, $column)) AS INTEGER)",
            'sqlite' => "(CAST(strftime('%Y', 'now') AS INTEGER) - CAST(strftime('%Y', $column) AS INTEGER) - (strftime('%m-%d', 'now') < strftime('%m-%d', $column)))",
            default => "TIMESTAMPDIFF(YEAR, $column, CURDATE())",
        };
    }

    /** The date remains a bound parameter supplied by the caller. */
    public static function dateDistance(string $column): string
    {
        $column = self::column($column);
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "ABS(CAST($column AS DATE) - CAST(? AS DATE))",
            'sqlite' => "ABS(julianday(date($column)) - julianday(date(?)))",
            default => "ABS(DATEDIFF($column, ?))",
        };
    }

    public static function jsonText(string $column): string
    {
        $column = self::column($column);
        return DB::connection()->getDriverName() === 'pgsql' ? "CAST($column AS TEXT)" : $column;
    }

    /** Preserve legacy MySQL numeric aggregation of the text prize label column. */
    public static function numericText(string $column): string
    {
        $column = self::column($column);
        return DB::connection()->getDriverName() === 'pgsql'
            ? "COALESCE(CAST(SUBSTRING($column FROM '^[[:space:]]*[+-]?[0-9]*[.]?[0-9]+') AS NUMERIC), 0)"
            : "CAST($column AS DECIMAL(20, 2))";
    }

    private static function column(string $column): string
    {
        if (! preg_match('/^[a-z_][a-z0-9_]*(\.[a-z_][a-z0-9_]*)?$/i', $column)) {
            throw new InvalidArgumentException('Invalid SQL column.');
        }
        return $column;
    }
}
