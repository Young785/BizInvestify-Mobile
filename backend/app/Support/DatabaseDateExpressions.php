<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class DatabaseDateExpressions
{
    public static function monthKey(string $column = 'created_at', string $alias = 'month'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("strftime('%Y-%m', {$column}) as {$alias}"),
            'pgsql' => DB::raw("to_char({$column}, 'YYYY-MM') as {$alias}"),
            default => DB::raw("DATE_FORMAT({$column}, '%Y-%m') as {$alias}"),
        };
    }

    public static function dateKey(string $column = 'created_at', string $alias = 'date'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("date({$column}) as {$alias}"),
            default => DB::raw("DATE({$column}) as {$alias}"),
        };
    }

    public static function hour(string $column = 'created_at', string $alias = 'hour'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("cast(strftime('%H', {$column}) as integer) as {$alias}"),
            'pgsql' => DB::raw("extract(hour from {$column}) as {$alias}"),
            default => DB::raw("HOUR({$column}) as {$alias}"),
        };
    }

    public static function dayOfWeek(string $column = 'created_at', string $alias = 'day'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("(cast(strftime('%w', {$column}) as integer) + 1) as {$alias}"),
            'pgsql' => DB::raw("extract(dow from {$column}) + 1 as {$alias}"),
            default => DB::raw("DAYOFWEEK({$column}) as {$alias}"),
        };
    }

    public static function monthNumber(string $column = 'created_at', string $alias = 'month'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("cast(strftime('%m', {$column}) as integer) as {$alias}"),
            'pgsql' => DB::raw("extract(month from {$column}) as {$alias}"),
            default => DB::raw("MONTH({$column}) as {$alias}"),
        };
    }

    public static function yearKey(string $column = 'created_at', string $alias = 'period'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("strftime('%Y', {$column}) as {$alias}"),
            'pgsql' => DB::raw("to_char({$column}, 'YYYY') as {$alias}"),
            default => DB::raw("DATE_FORMAT({$column}, '%Y') as {$alias}"),
        };
    }

    public static function weekKey(string $column = 'created_at', string $alias = 'period'): Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => DB::raw("strftime('%Y-W%W', {$column}) as {$alias}"),
            'pgsql' => DB::raw("to_char({$column}, 'IYYY-\"W\"IW') as {$alias}"),
            default => DB::raw("DATE_FORMAT({$column}, '%x-W%v') as {$alias}"),
        };
    }

    public static function periodKey(string $period, string $column = 'created_at', string $alias = 'period'): Expression
    {
        return match ($period) {
            'daily' => self::dateKey($column, $alias),
            'weekly' => self::weekKey($column, $alias),
            'yearly' => self::yearKey($column, $alias),
            default => self::monthKey($column, $alias),
        };
    }
}
