<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionLevelResource\Pages;
use App\Filament\Resources\CommissionLevelResource\RelationManagers;
use App\Models\CommissionLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommissionLevelResource extends Resource
{
    protected static ?string $model = CommissionLevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Settings';

    public static function getNavigationUrl(): string
    {
        return '/admin/commission-levels/1/edit';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Level 1')->schema([
                    Forms\Components\TextInput::make('level_1_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_1_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 2')->schema([
                    Forms\Components\TextInput::make('level_2_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_2_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 3')->schema([
                    Forms\Components\TextInput::make('level_3_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_3_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 4')->schema([
                    Forms\Components\TextInput::make('level_4_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_4_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 5')->schema([
                    Forms\Components\TextInput::make('level_5_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_5_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 6')->schema([
                    Forms\Components\TextInput::make('level_6_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_6_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 7')->schema([
                    Forms\Components\TextInput::make('level_7_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_7_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
                Forms\Components\Section::make('Level 8')->schema([
                    Forms\Components\TextInput::make('level_8_target')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('level_8_commission')
                        ->required()
                        ->numeric(),
                ])->columnSpan(1),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('level_1_target')
                    ->label('Level 1')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_1_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_2_target')
                    ->label('Level 2')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_2_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_3_target')
                    ->label('Level 3')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_3_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_4_target')
                    ->label('Level 4')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_4_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_5_target')
                    ->label('Level 5')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_5_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_6_target')
                    ->label('Level 6')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_6_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_7_target')
                    ->label('Level 7')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_7_commission . '%')
                    ->numeric(),
                Tables\Columns\TextColumn::make('level_8_target')
                    ->label('Level 8')
                    ->prefix('Target: ')
                    ->description(fn(CommissionLevel $record) => 'Commission: '. $record->level_8_commission . '%')
                    ->numeric(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListCommissionLevels::route('/'),
            'create' => Pages\CreateCommissionLevel::route('/create'),
            'edit' => Pages\EditCommissionLevel::route('/{record}/edit'),
        ];
    }
}
