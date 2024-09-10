<?php

namespace App\Filament\Widgets;

use App\Enums\UserType;
use App\Models\Purchase;
use App\Models\SpecialRequest;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $start = $this->filters['start_date'] ?? now()->startOfDay();
        $end = $this->filters['end_date'] ?? now()->endOfDay();

        $purchaseQuery = Purchase::query()->whereBetween('created_at', [$start, $end]);
        if ($this->filters['type']) {
            $purchaseQuery->whereHas('filter',function ($query) {
                $query->whereHas('collection', function ($query) {
                    $query->where('type', $this->filters['type']);
                });
            });
        }
        $revenueData = DashboardService::getData(Trend::query($purchaseQuery), array_merge($this->filters,['period' => 'custom']))->map(fn ($value) => $value->aggregate)->toArray();

        $specialOrderQuery = SpecialRequest::query()->whereBetween('created_at', [$start, $end]);
        if ($this->filters['type']) {
            $specialOrderQuery->where('platform', $this->filters['type']);
        }
        $specialOrderData = DashboardService::getData(Trend::query($specialOrderQuery), array_merge($this->filters,['period' => 'custom']))->map(fn ($value) => $value->aggregate)->toArray();

        return [
            Stat::make('Revenue', DashboardService::formatNumber($purchaseQuery->sum('amount')))->description('32k increase')
                ->chart($revenueData)
                ->chartColor('success'),
            Stat::make('Purchased Filter', DashboardService::formatNumber($purchaseQuery->count()))
                ->chart($revenueData)
                ->chartColor('success'),
            Stat::make('Special Order', DashboardService::formatNumber($specialOrderQuery->count()))
                ->chart($specialOrderData)
                ->chartColor('success'),
        ];
    }
}
