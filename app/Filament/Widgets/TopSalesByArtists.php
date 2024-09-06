<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSalesByArtists extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                UserResource::getEloquentQuery()->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->default(fn($record) => 'https://ui-avatars.com/api/?length=1&name=' . urlencode($record->name))->width(40),
                Tables\Columns\TextColumn::make('income')->label('User')
                    ->state('$8.45k')->description(fn($record) => $record->username),
                Tables\Columns\TextColumn::make('result')->label('Trend')
                    ->iconPosition('after')
                    ->color('success')
                    ->state('25.3%')->icon('heroicon-o-arrow-up-right'),
            ])
            ->paginated(false);
    }
}
