<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CountryResource;
use App\Filament\Resources\UserResource;
use App\Models\Purchase;
use App\Services\DashboardService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSalesByCountries extends BaseWidget
{
    use InteractsWithPageFilters;

    public function table(Table $table): Table
    {
        $start = $this->filters['start_date'] ?? now()->startOfDay();
        $end = $this->filters['end_date'] ?? now()->endOfDay();

        $total = Purchase::query()
            ->when($this->filters['start_date'], fn($query) => $query->where('created_at', '>=', $start))
            ->when($this->filters['end_date'], fn($query) => $query->where('created_at', '<=', $end))
            ->sum('amount');

        return $table
            ->query(
                CountryResource::getEloquentQuery()
                    ->select('countries.id', 'countries.name', 'countries.image')
                    ->selectRaw('SUM(purchases.amount) as total_sales')
                    ->join('users', 'countries.id', '=', 'users.country_id')
                    ->join('purchases', 'users.id', '=', 'purchases.artist_id')
                    ->when($this->filters['start_date'], fn($query) => $query->where('purchases.created_at', '>=', $start))
                    ->when($this->filters['end_date'], fn($query) => $query->where('purchases.created_at', '<=', $end))
                    ->groupBy('countries.id', 'countries.name')
                    ->orderByDesc('total_sales')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->width(40),
                Tables\Columns\TextColumn::make('total_sales')->label('Revenue')
                    ->money()
                    ->state(fn($record) => DashboardService::formatNumber($record->total_sales / 100))
                   ->description(fn($record) => $record->name),
                Tables\Columns\TextColumn::make('percent')
                    ->color('success')
                    ->state(fn($record) => $total ? round(($record->total_sales / $total) * 100) . '%' : '0%'),
            ])
            ->paginated(false);
    }
}
