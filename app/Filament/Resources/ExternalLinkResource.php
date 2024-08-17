<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternalLinkResource\Pages;
use App\Filament\Resources\ExternalLinkResource\RelationManagers;
use App\Models\ExternalLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExternalLinkResource extends Resource
{
    protected static ?string $model = ExternalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Settings';

    public static function getNavigationUrl(): string
    {
        return '/admin/external-links/1/edit';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('terms_of_use')
                    ->url()
                    ->rule('required')
                    ->columnSpanFull()
                    ->markAsRequired(),
                Forms\Components\TextInput::make('privacy_policy')
                    ->url()
                    ->rule('required')
                    ->columnSpanFull()
                    ->markAsRequired(),
                Forms\Components\TextInput::make('help_center')
                    ->url()
                    ->rule('required')
                    ->columnSpanFull()
                    ->markAsRequired(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('terms_of_use')->wrap(),
                Tables\Columns\TextColumn::make('privacy_policy')->wrap(),
                Tables\Columns\TextColumn::make('help_center')->wrap(),
            ])
            ->paginated(false)
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListExternalLinks::route('/'),
            'create' => Pages\CreateExternalLink::route('/create'),
            'edit' => Pages\EditExternalLink::route('/{record}/edit'),
        ];
    }
}
