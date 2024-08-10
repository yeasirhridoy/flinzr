<?php

namespace App\Filament\Resources;

use App\Enums\PlatformType;
use App\Enums\SalesType;
use App\Enums\UserType;
use App\Filament\Resources\CollectionResource\Pages;
use App\Filament\Resources\CollectionResource\RelationManagers;
use App\Models\Collection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Collection Info')->schema([
                        Forms\Components\Group::make([
                            Forms\Components\ToggleButtons::make('type')->options(PlatformType::class)->required()->inline()->columnSpan(2),
                            Forms\Components\ToggleButtons::make('sales_type')->options(SalesType::class)->required()->inline(),
                        ])->columns(3)->columnSpanFull(),
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->relationship('category', 'eng_name')
                                ->preload()
                                ->required()
                                ->searchable(),
                            Forms\Components\Select::make('colors')
                                ->label('Colors')
                                ->relationship('colors', 'eng_name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Forms\Components\Select::make('tags')
                                ->label('Tags')
                                ->relationship('tags', 'eng_name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Forms\Components\Select::make('countries')
                                ->label('Countries')
                                ->relationship('countries', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Forms\Components\Select::make('user_id')
                            ->label('Artist')
                            ->relationship('user', 'name',function (Builder $query) {
                                $query->where('type',UserType::Artist);
                            })
                        ])->columns(5)->columnSpanFull(),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('eng_name')->label('Name (English)'),
                            Forms\Components\Textarea::make('eng_description')->label('Description (English)'),
                        ]),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('arabic_name')->label('Name (Arabic)'),
                            Forms\Components\Textarea::make('arabic_description')->label('Description (Arabic)'),
                        ]),
                        Forms\Components\Group::make([
                            Forms\Components\FileUpload::make('avatar')->image()->imageEditor(),
                            Forms\Components\FileUpload::make('thumbnail')->image()->imageEditor(),
                            Forms\Components\FileUpload::make('cover')->image()->imageEditor(),
                        ])->columns(3)->columnSpanFull(),
                    ])->columns(2),
                    Forms\Components\Wizard\Step::make('Filters')->schema([
                        Forms\Components\Repeater::make('filters')
                            ->relationship()
                            ->addActionLabel('Add Filter')
                            ->maxItems(8)
                            ->grid(4)
                            ->schema([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('url')->url()->required(),
                                Forms\Components\FileUpload::make('image')->image()->imageEditor(),
                            ])
                    ])->columnSpanFull()
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            Submit
                        </x-filament::button>
                        BLADE)))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')->circular(),
                Tables\Columns\TextColumn::make('eng_name')->searchable()
                    ->sortable()->label('Name'),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()->searchable()->label('Platform')->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()->searchable()->label('Artist'),
                Tables\Columns\TextColumn::make('filters_count')
                    ->sortable()->counts('filters')->label('Filters')->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()->label('Created At')->since(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable()->label('Active'),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->sortable()->label('Featured'),
            ])
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
//            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
