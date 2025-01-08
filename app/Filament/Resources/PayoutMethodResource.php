<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutMethodResource\Pages;
use App\Filament\Resources\PayoutMethodResource\RelationManagers;
use App\Models\PayoutMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayoutMethodResource extends Resource
{
    protected static ?string $model = PayoutMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->markAsRequired()
                    ->unique('payout_methods', 'user_id', ignoreRecord: true)
                    ->disabledOn('edit'),
                Forms\Components\Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->markAsRequired(),
                Forms\Components\TextInput::make('full_name')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('phone')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('id_no')
                    ->label('ID No')
                    ->markAsRequired(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->label('User'),
                Tables\Columns\TextColumn::make('country.name')->searchable()->label('Country'),
                Tables\Columns\TextColumn::make('full_name')->searchable()->label('Full Name'),
                Tables\Columns\TextColumn::make('id_no')->searchable()->label('ID No'),
                Tables\Columns\TextColumn::make('phone')->searchable()->label('Phone'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPayoutMethods::route('/'),
            'create' => Pages\CreatePayoutMethod::route('/create'),
            'edit' => Pages\EditPayoutMethod::route('/{record}/edit'),
        ];
    }
}
