<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CountryResource;
use App\Filament\Resources\UserResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSalesByCountries extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CountryResource::getEloquentQuery()->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->width(40),
                Tables\Columns\TextColumn::make('income')->label('User')
                    ->state('$8.45k')->description(fn($record) => $record->name),
                Tables\Columns\TextColumn::make('result')->label('Trend')
                    ->iconPosition('after')
                    ->color('success')
                    ->state('25.3%')->icon('heroicon-o-arrow-up-right'),
            ])
            ->paginated(false);
    }
}
