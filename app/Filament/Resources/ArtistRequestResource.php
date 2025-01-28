<?php

namespace App\Filament\Resources;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Enums\UserType;
use App\Filament\Resources\ArtistRequestResource\Pages;
use App\Filament\Resources\ArtistRequestResource\RelationManagers;
use App\Models\ArtistRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArtistRequestResource extends Resource
{
    protected static ?string $model = ArtistRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Requests';

    protected static ?string $label = 'Artist';

    public static function getNavigationBadge(): ?string
    {
        return ArtistRequest::where('status', RequestStatus::Pending)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->rule('required')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('url'),
                Forms\Components\Select::make('platform')->options(PlatformType::class)->rule('required')
                    ->markAsRequired(),
                Forms\Components\ToggleButtons::make('status')
                    ->options(RequestStatus::class)
                    ->default(RequestStatus::Pending)
                    ->inline()
                    ->rule('required')
                    ->markAsRequired(),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->description(fn($record) => $record->user?->username ?? 'Deleted User')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.country.name')
                    ->limit()
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Request Id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options(RequestStatus::class)
                    ->sortable()
                    ->afterStateUpdated(function (ArtistRequest $record, $state) {
                        if ($state === RequestStatus::Complete->value) {
                            $record->user()->update(['type' => UserType::Artist]);
                        } else {
                            $record->user()->update(['type' => UserType::Customer]);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Visit')->icon('heroicon-o-arrow-top-right-on-square')->url(fn($record) => $record->url,true),
                Tables\Actions\ViewAction::make()
            ])
            ->recordAction(null)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('url')->url(fn($record) => $record->url,true),
                IconEntry::make('platform')
                    ->label('Platform'),
                TextEntry::make('status')
            ])->columns(4);
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
            'index' => Pages\ListArtistRequests::route('/'),
//            'create' => Pages\CreateArtistRequest::route('/create'),
//            'view' => Pages\ViewArtistRequest::route('/{record}'),
//            'edit' => Pages\EditArtistRequest::route('/{record}/edit'),
        ];
    }
}
