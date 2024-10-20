<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\DashboardService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSalesByArtists extends BaseWidget
{
    use InteractsWithPageFilters;

    public function table(Table $table): Table
    {
        $start = $this->filters['start_date'] ?? now()->startOfDay();
        $end = $this->filters['end_date'] ?? now()->endOfDay();

        return $table
            ->query(
                UserResource::getEloquentQuery()
                    ->select('users.id', 'users.name', 'users.username', 'users.level')
                    ->join('purchases', 'users.id', '=', 'purchases.artist_id')
                    ->groupBy('users.id')
                    ->selectRaw('SUM(purchases.earning) as total_earned')
                    ->when($this->filters['start_date'], fn($query) => $query->where('purchases.created_at', '>=', $start))
                    ->when($this->filters['end_date'], fn($query) => $query->where('purchases.created_at', '<=', $end))
                    ->orderByDesc('total_earned')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->state(fn($record) => 'https://ui-avatars.com/api/?length=1&name=' . urlencode($record->name))->width(40),
                Tables\Columns\TextColumn::make('total_earned')
                    ->label('Revenue')
                    ->money()
                    ->state(fn($record) => $record->total_earned / 100)
                    ->description(fn($record) => $record->username),
                Tables\Columns\TextColumn::make('level')->badge(),
            ])
            ->paginated(false);
    }
}
