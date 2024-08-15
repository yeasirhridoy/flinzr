<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\SpecialRequestResource\Pages;
use App\Filament\Resources\SpecialRequestResource\RelationManagers;
use App\Models\SpecialRequest;
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

class SpecialRequestResource extends Resource
{
    protected static ?string $model = SpecialRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Requests';

    protected static ?string $label = 'Special';

    public static function getNavigationBadge(): ?string
    {
        return SpecialRequest::where('status', RequestStatus::Pending)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.country.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
                Tables\Columns\SelectColumn::make('status')->options(RequestStatus::class)->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Conversations')->url(fn(SpecialRequest $record) => route('filament.admin.resources.special-requests.conversations',$record->id)),
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
                    TextEntry::make('platform')->badge(),
                    TextEntry::make('category.eng_name'),
                    TextEntry::make('occasion')
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
            'index' => Pages\ListSpecialRequests::route('/'),
            'conversations' => Pages\SpecialRequestConversations::route('/{record}/conversations')
//            'create' => Pages\CreateSpecialRequest::route('/create'),
//            'edit' => Pages\EditSpecialRequest::route('/{record}/edit'),
        ];
    }
}
