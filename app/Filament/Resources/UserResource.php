<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\FileUpload::make('image')
                    ->image()->avatar()->imageEditor()->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->required(),
                Forms\Components\Group::make([
                    Forms\Components\Select::make('country_id')
                        ->relationship('country', 'name')
                        ->preload()
                        ->required()
                        ->searchable(),
                    Forms\Components\ToggleButtons::make('type')
                        ->options(UserType::class)
                        ->inline()
                        ->default(UserType::Customer)
                        ->required(),
                    Forms\Components\ToggleButtons::make('status')
                        ->options(UserStatus::class)
                        ->inline()
                        ->required()
                        ->default(UserStatus::Active),
                ])->columns(3)->columnSpanFull(),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('balance')
                        ->numeric(),
                    Forms\Components\TextInput::make('coin')
                        ->numeric(),
                ])->columns(2),
                Forms\Components\TextInput::make('password')
                    ->visibleOn(['create'])
                    ->minLength(6)
                    ->password()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn(User $record) => $record->email)
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money()
                    ->prefix('$')
                    ->badge()
                    ->color('success')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\ToggleColumn::make('is_admin')
                    ->label('Admin'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}