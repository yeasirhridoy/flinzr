<?php

namespace App\Services;

use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public static function getStartDate($filters): Carbon
    {
        $period = $filters['period'] ?? 'today';
        return match ($period) {
            'week' => Carbon::now()->subDays(7)->startOfDay(),
            'yesterday' => Carbon::now()->subDay()->startOfDay(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            'custom' => Carbon::parse($filters['start_date'] ?? now()->startOfDay()),
            default => now()->startOfDay(),
        };
    }

    public static function getEndDate($filters): Carbon
    {
        $period = $filters['period'] ?? 'today';
        return match ($period) {
            'week' => Carbon::now(),
            'yesterday' => Carbon::now()->subDay()->endOfDay(),
            'month' => now()->endOfMonth(),
            'year' => now()->endOfYear(),
            'custom' => Carbon::parse($filters['end_date'] ?? now()->endOfDay()),
            default => now()->endOfDay(),
        };
    }
    public static function getInterval(Carbon $start, Carbon $end): string
    {
        $diff = $start->diffInDays($end);

        return $diff <= 1 ? 'perHour' : ($diff <= 31 ? 'perDay' : ($diff <= 365 ? 'perMonth' : 'perYear'));
    }

    public static function formatNumber(int $number): string
    {
        if ($number < 1000) {
            return number_format($number);
        }

        $units = ['', 'k', 'M', 'B', 'T'];
        $unit = floor((strlen($number) - 1) / 3);
        $abbreviated = $number / pow(1000, $unit);

        return number_format($abbreviated, 2).$units[$unit];
    }

    public static function getData(Trend $trend, ?array $filters): Collection
    {
        $start = self::getStartDate($filters);
        $end = self::getEndDate($filters);
        $interval = self::getInterval($start, $end);
        return $trend->between(start: $start, end: $end)->$interval()->count();
    }
}
