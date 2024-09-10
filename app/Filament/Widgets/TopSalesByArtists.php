<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
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
                    ->selectRaw('SUM(purchases.earning) as total_earned')
                    ->join('purchases', 'users.id', '=', 'purchases.artist_id')
                    ->whereBetween('purchases.created_at', [$start, $end])
                    ->groupBy('users.id', 'users.username')
                    ->orderByDesc('total_earned')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->default(fn($record) => 'https://ui-avatars.com/api/?length=1&name=' . urlencode($record->name))->width(40),
                Tables\Columns\TextColumn::make('total_earned')->label('User')->money()->description(fn($record) => $record->username),
                Tables\Columns\TextColumn::make('level')->badge(),
            ])
            ->paginated(false);
    }
}
