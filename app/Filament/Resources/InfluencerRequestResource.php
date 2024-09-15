<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\InfluencerRequestResource\Pages;
use App\Filament\Resources\InfluencerRequestResource\RelationManagers;
use App\Models\InfluencerRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InfluencerRequestResource extends Resource
{
    protected static ?string $model = InfluencerRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Requests';

    protected static ?string $label = 'Influencer';

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
                Tables\Columns\TextColumn::make('user.name')
                    ->description(fn($record) => $record->user->username)
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Request Id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->sortable()
                    ->options(RequestStatus::class)
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
                    TextEntry::make('snapchat')->badge()->color(Color::Yellow),
                    TextEntry::make('instagram')->badge()->color(Color::Pink),
                    TextEntry::make('tiktok')->badge()->color(Color::Purple),
                ])->columnSpanFull()->columns(3)
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
