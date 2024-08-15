<?php

namespace App\Filament\Resources;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Filament\Resources\ArtistRequestResource\Pages;
use App\Filament\Resources\ArtistRequestResource\RelationManagers;
use App\Models\ArtistRequest;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->required(),
                Forms\Components\TextInput::make('url'),
                Forms\Components\Select::make('platform')->options(PlatformType::class)->required(),
                Forms\Components\ToggleButtons::make('status')
                    ->options(RequestStatus::class)
                    ->default(RequestStatus::Pending)
                    ->inline()
                    ->required(),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.country.name')
                    ->limit()
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options(RequestStatus::class)
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Visit Portfolio')
                    ->url(fn (ArtistRequest $record) => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Section::make()->schema([
                    TextEntry::make('name'),
                    TextEntry::make('status'),
                ])
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
            'index' => Pages\ListArtistRequests::route('/'),
//            'create' => Pages\CreateArtistRequest::route('/create'),
//            'view' => Pages\ViewArtistRequest::route('/{record}'),
//            'edit' => Pages\EditArtistRequest::route('/{record}/edit'),
        ];
    }
}
