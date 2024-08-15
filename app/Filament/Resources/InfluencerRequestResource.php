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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->sortable()
                    ->options(RequestStatus::class)
            ])
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
                    TextEntry::make('snapchat')->badge(),
                    TextEntry::make('instagram')->badge(),
                    TextEntry::make('tiktok')->badge(),
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
