<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Http\Controllers\SubscriptionController;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\ForceDeleteAction;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('type')
                    ->options(UserType::class)
                    ->inline()
                    ->default(UserType::Customer)
                    ->rule('required')
                    ->markAsRequired(),
                Forms\Components\Select::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->rule('required')
                    ->markAsRequired()
                    ->searchable(),
                Forms\Components\TextInput::make('coin')
                    ->default(25)
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->rule('required')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('username')
                    ->rule('required')
                    ->unique(ignoreRecord: true)
                    ->regex('/^[a-zA-Z0-9_]+$/')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('email')
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->rule('required')
                    ->markAsRequired(),
                Forms\Components\TextInput::make('balance')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('password')
                    ->visibleOn(['create'])
                    ->minLength(6)
                    ->password()
                    ->rule('required')
                    ->markAsRequired(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereNot('email', 'devoartsa@gmail.com');
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->state(fn(User $record) => 'https://ui-avatars.com/api/?length=1&name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->label('User')
                    ->description(fn(User $record) => $record->username)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('subscribed')
                    ->boolean()
                    ->getStateUsing(function (User $record) {
                        $date = SubscriptionController::checkSubscriptionValidity($record->username);
                        return $date && Carbon::parse($date)->isFuture();
                    })->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable()
                    ->label('Active'),
                Tables\Columns\ToggleColumn::make('is_admin')
                    ->sortable()
                    ->label('Editor')->visible(fn() => auth()->user()->email == 'devoartsa@gmail.com'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->columnSpanFull()
                    ->placeholder('All users')
                    ->trueLabel('Verified users')
                    ->falseLabel('Not verified users')
                    ->nullable(),
                Tables\Filters\TrashedFilter::make()->columnSpanFull(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('start_date'),
                        Forms\Components\DatePicker::make('end_date'),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })->columnSpan(2),
                Tables\Filters\SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->label('Country')
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\Action::make('reset device')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->devices()->update(['device_details' => null, 'device_added_at' => null])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
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
                Section::make([
                    TextEntry::make('type')->badge(),
                    TextEntry::make('username')->badge(),
                    TextEntry::make('email'),
                    TextEntry::make('coin')->badge()->color('primary'),
                    TextEntry::make('balance')->money()->badge()->color('success'),
                    TextEntry::make('level')->badge()->color('info')->visible(fn(User $record) => $record->type === UserType::Artist),
                ])->columns(3),
                Section::make([
                    TextEntry::make('influencerRequest.snapchat')->badge()->label('Snapchat')->color(Color::Yellow),
                    TextEntry::make('influencerRequest.tiktok')->badge()->label('TikTok')->color(Color::Purple),
                    TextEntry::make('influencerRequest.instagram')->badge()->label('Instagram')->color(Color::Pink),
                ])->columns(3)->visible(fn(User $record) => $record->type === UserType::Influencer),
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
