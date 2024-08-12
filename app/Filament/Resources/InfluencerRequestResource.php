<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\InfluencerRequestResource\Pages;
use App\Filament\Resources\InfluencerRequestResource\RelationManagers;
use App\Models\InfluencerRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InfluencerRequestResource extends Resource
{
    protected static ?string $model = InfluencerRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Requests';

    public static function getNavigationBadge(): ?string
    {
        return InfluencerRequest::where('status', RequestStatus::Pending)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('snapchat')->sortable()->searchable()->badge()->color(Color::Yellow),
                Tables\Columns\TextColumn::make('instagram')->sortable()->searchable()->badge()->color(Color::Pink),
                Tables\Columns\TextColumn::make('tiktok')->sortable()->searchable()->badge()->color(Color::Purple),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options(RequestStatus::class)
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfluencerRequests::route('/'),
//            'create' => Pages\CreateInfluencerRequest::route('/create'),
//            'edit' => Pages\EditInfluencerRequest::route('/{record}/edit'),
        ];
    }
}
