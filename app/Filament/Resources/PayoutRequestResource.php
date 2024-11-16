<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\PayoutRequestResource\Pages;
use App\Filament\Resources\PayoutRequestResource\RelationManagers;
use App\Models\PayoutRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Requests';

    protected static ?string $label = 'Payout';

    public static function getNavigationBadge(): ?string
    {
        return PayoutRequest::where('status', RequestStatus::Pending)->count();
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
                    ->description(fn($record) => $record->user?->username ?? 'Deleted User')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Request Id'),
                Tables\Columns\TextColumn::make('amount')->sortable()->money(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->sortable()
                    ->disabled(fn($record) => $record->status !== RequestStatus::Pending->value)
                    ->afterStateUpdated(function(PayoutRequest $record, $state) {
                        if ($state === RequestStatus::Complete->value) {
                            if($record->user->balance >= $record->amount) {
                                $record->user()->update([
                                    'balance' => ($record->user->balance - $record->amount) * 100
                                ]);
                            } else {
                                Notification::make()
                                    ->title('Insufficient Balance')
                                    ->warning()
                                    ->send();
                                $record->update([
                                    'status' => RequestStatus::Pending
                                ]);
                            }
                        }
                    })
                    ->options(RequestStatus::class),
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
                    TextEntry::make('full_name')->label('Beneficiary'),
                    TextEntry::make('id_no')->label('Id No.'),
                    TextEntry::make('phone')->label('Mobile No.'),
                    TextEntry::make('user.balance')->label('Balance')->badge()->money()->color('success'),
                ])->columnSpanFull()->columns(2)
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
            'index' => Pages\ListPayoutRequests::route('/'),
//            'create' => Pages\CreatePayoutRequest::route('/create'),
//            'edit' => Pages\EditPayoutRequest::route('/{record}/edit'),
        ];
    }
}
