<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\SpecialRequestResource\Pages;
use App\Filament\Resources\SpecialRequestResource\RelationManagers;
use App\Models\SpecialRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class SpecialRequestResource extends Resource
{
    protected static ?string $model = SpecialRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift-top';

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
                Tables\Columns\TextColumn::make('user.name')
                    ->description(fn($record) => $record->user->username)
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.country.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Request Id'),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
                Tables\Columns\SelectColumn::make('status')->options(RequestStatus::class)->searchable()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Chat')
                    ->icon('heroicon-o-chat-bubble-oval-left')
                    ->url(fn(SpecialRequest $record) => route('filament.admin.resources.special-requests.conversations', $record->id)),
                Tables\Actions\ViewAction::make()->label('Order')->icon('heroicon-o-briefcase'),
            ])
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
                Section::make()->schema([
                    TextEntry::make('platform')->badge(),
                    TextEntry::make('category.eng_name'),
                    TextEntry::make('occasion'),
                    Actions::make([
                        Action::make('download')
                            ->action(function (SpecialRequest $record) {
                                return Storage::download($record->image);
                            })
                            ->icon('heroicon-o-arrow-down-tray'),
                        Action::make('upload')
                            ->icon('heroicon-o-arrow-up-tray')
                            ->form([
                                Forms\Components\FileUpload::make('filter')
                                    ->image()
                                    ->directory('special-requests')
                                    ->default(fn(SpecialRequest $record) => $record->filter),
                            ])
                            ->action(function (array $data, SpecialRequest $record) {
                                $record->update([
                                    'filter' => $data['filter']
                                ]);
                            }),
                    ]),
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
            'conversations' => Pages\SpecialRequestConversations::route('/{record}/conversations'),
            'view' => Pages\ViewSpecialRequest::route('/{record}'),
//            'create' => Pages\CreateSpecialRequest::route('/create'),
//            'edit' => Pages\EditSpecialRequest::route('/{record}/edit'),
        ];
    }
}
