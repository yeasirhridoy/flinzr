<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('privacy_policy')->url(),
                Forms\Components\TextInput::make('review_app')->url(),
                Forms\Components\TextInput::make('help_center')->url(),
                Forms\Components\TextInput::make('become_an_artist')->url(),
                Forms\Components\TextInput::make('upload_request_terms')->url(),
                Forms\Components\TextInput::make('payout_request_terms')->url(),
                Forms\Components\TextInput::make('artist_commission_value')
                    ->numeric(),
                Forms\Components\TextInput::make('filter_price')
                    ->numeric(),
                Forms\Components\TextInput::make('special_request_price')
                    ->numeric(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('privacy_policy')
                    ->searchable(),
                Tables\Columns\TextColumn::make('review_app')
                    ->searchable(),
                Tables\Columns\TextColumn::make('help_center')
                    ->searchable(),
                Tables\Columns\TextColumn::make('become_an_artist')
                    ->searchable(),
                Tables\Columns\TextColumn::make('upload_request_terms')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payout_request_terms')
                    ->searchable(),
                Tables\Columns\TextColumn::make('artist_commission_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('filter_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('special_request_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
