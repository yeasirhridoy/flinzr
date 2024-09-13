<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CountryResource;
use App\Filament\Resources\UserResource;
use App\Models\Purchase;
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

        $total = Purchase::query()->whereBetween('created_at', [$start, $end])->sum('amount');

        return $table
            ->query(
                CountryResource::getEloquentQuery()
                    ->select('countries.id', 'countries.name', 'countries.image')
                    ->selectRaw('SUM(purchases.amount) as total_sales')
                    ->join('users', 'countries.id', '=', 'users.country_id')
                    ->join('purchases', 'users.id', '=', 'purchases.artist_id')
                    ->whereBetween('purchases.created_at', [$start, $end])
                    ->groupBy('countries.id', 'countries.name')
                    ->orderByDesc('total_sales')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->width(40),
                Tables\Columns\TextColumn::make('total_sales')->label('Revenue')
                    ->money()
                   ->description(fn($record) => $record->name),
                Tables\Columns\TextColumn::make('percent')
                    ->color('success')
                    ->state(fn($record) => round($record->total_sales / $total * 100) . '%'),
            ])
            ->paginated(false);
    }
}
