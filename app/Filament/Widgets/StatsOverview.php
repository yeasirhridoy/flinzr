<?php

namespace App\Filament\Widgets;

use App\Enums\UserType;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active Customers', User::query()->where('is_active',true)->where('type',UserType::Customer)->count()),
            Stat::make('Active Artists', User::query()->where('is_active',true)->where('type',UserType::Artist)->count()),
            Stat::make('Active Influencer', User::query()->where('is_active',true)->where('type',UserType::Influencer)->count()),
            Stat::make('Revenue', '$122.01k')->description('32k increase')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->chartColor('success')
                ->descriptionIcon('heroicon-o-arrow-trending-up')->descriptionColor('success'),
            Stat::make('Revenue', '$122.01k')->description('32k increase')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->chartColor('danger')
                ->descriptionIcon('heroicon-o-arrow-trending-down')->descriptionColor('danger'),
            Stat::make('Revenue', '$122.01k')->description('32k increase')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->chartColor('success')
                ->descriptionIcon('heroicon-o-arrow-trending-up')->descriptionColor('success'),
        ];
    }
}
