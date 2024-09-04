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
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
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
                            Forms\Components\ToggleButtons::make('type')->options(PlatformType::class)->rule('required')
                                ->markAsRequired()->inline()->columnSpan(2),
                            Forms\Components\ToggleButtons::make('sales_type')->options(SalesType::class)->rule('required')
                                ->markAsRequired()->inline(),
                        ])->columns(3)->columnSpanFull(),
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->relationship('category', 'eng_name')
                                ->preload()
                                ->rule('required')
                                ->markAsRequired()
                                ->searchable(),
                            Forms\Components\Select::make('colors')
                                ->label('Colors')
                                ->relationship('colors', 'eng_name')
                                ->multiple()
                                ->preload()
                                ->rule('required')
                                ->markAsRequired()
                                ->searchable(),
                            Forms\Components\Select::make('tags')
                                ->label('Tags')
                                ->relationship('tags', 'eng_name')
                                ->multiple()
                                ->preload()
                                ->rule('required')
                                ->markAsRequired()
                                ->searchable(),
                            Forms\Components\Select::make('regions')
                                ->label('Regions')
                                ->relationship('regions', 'name')
                                ->multiple()
                                ->preload()
                                ->rule('required')
                                ->markAsRequired()
                                ->searchable(),
                            Forms\Components\Select::make('user_id')
                                ->label('Artist')
                                ->rule('required')
                                ->markAsRequired()
                                ->searchable()
                                ->preload()
                                ->relationship('user', 'name', function (Builder $query) {
                                    $query->where('type', UserType::Artist);
                                })
                        ])->columns(5)->columnSpanFull(),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('eng_name')
                                ->rule('required')
                                ->markAsRequired()
                                ->label('Name (English)'),
                            Forms\Components\Textarea::make('eng_description')
                                ->rule('required')
                                ->markAsRequired()
                                ->label('Description (English)'),
                        ]),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('arabic_name')
                                ->rule('required')
                                ->markAsRequired()
                                ->label('Name (Arabic)'),
                            Forms\Components\Textarea::make('arabic_description')
                                ->rule('required')
                                ->markAsRequired()
                                ->label('Description (Arabic)'),
                        ]),
                        Forms\Components\Group::make([
                            Forms\Components\FileUpload::make('avatar')
                                ->required()->image()->imageEditor(),
                            Forms\Components\FileUpload::make('thumbnail')
                                ->required()->image()->imageEditor(),
                            Forms\Components\FileUpload::make('cover')
                                ->required()->image()->imageEditor(),
                        ])->columns(3)->columnSpanFull(),
                    ])->columns(2),
                    Forms\Components\Wizard\Step::make('Filters')->schema([
                        Forms\Components\Repeater::make('filters')
                            ->relationship()
                            ->addActionLabel('Add Filter')
                            ->maxItems(8)
                            ->grid(4)
                            ->schema([
                                Forms\Components\TextInput::make('name')->rule('required')
                                    ->markAsRequired(),
                                Forms\Components\TextInput::make('url')->url()->rule('required')
                                    ->markAsRequired(),
                                Forms\Components\FileUpload::make('image')
                                    ->required()->image()->imageEditor(),
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
                        BLADE
                    )))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('eng_name')->searchable()
                    ->sortable()->label('Name')->wrap(),
                Tables\Columns\TextColumn::make('user.name')
                    ->wrap()
                    ->sortable()->searchable()->label('Artist'),
                Tables\Columns\TextColumn::make('filters_count')
                    ->sortable()->counts('filters')->label('Filters')->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()->since(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable()->label('Active'),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->sortable()->label('Featured'),
                Tables\Columns\ToggleColumn::make('is_trending')
                    ->sortable()->label('Trending'),
            ])
            ->reorderable('order_column')
            ->defaultSort('updated_at', 'desc')
            ->filters([
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
                Tables\Filters\SelectFilter::make('sales_type')
                    ->options(SalesType::class),
                Tables\Filters\SelectFilter::make('regions')
                    ->relationship('regions', 'name')
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Approve')->color('success'),
                    Tables\Actions\Action::make('Reject')->color('danger'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

//    public static function infolist(Infolist $infolist): Infolist
//    {
//        return $infolist
//            ->schema([
//                Section::make('Collection Info')->schema([
//                    Group::make([
//                        TextEntry::make('user.name')->label('Artist'),
//                        TextEntry::make('type'),
//                        TextEntry::make('sales_type'),
//                    ]),
//                    Group::make([
//                        TextEntry::make('category.name'),
//                        TextEntry::make('tags.name'),
//                        ColorEntry::make('colors.code'),
//                        TextEntry::make('regions.name'),
//                    ]),
//                    Group::make([
//                        TextEntry::make('eng_name')->label('Name (English)'),
//                        TextEntry::make('eng_description')->label('Description (English)'),
//                    ]),
//                    Group::make([
//                        TextEntry::make('arabic_name')->label('Name (Arabic)'),
//                        TextEntry::make('arabic_description')->label('Description (Arabic)'),
//                    ]),
//                    Group::make([
//                        ImageEntry::make('avatar'),
//                        ImageEntry::make('thumbnail'),
//                        ImageEntry::make('cover'),
//                    ])->columns(1)->columnSpanFull()
//                ])->columns(4),
//                Section::make('Filters')->schema([
//                    RepeatableEntry::make('filters')
//                        ->hiddenLabel()
//                        ->columnSpanFull()
//                        ->schema([
//                            TextEntry::make('name'),
//                            TextEntry::make('url'),
//                            ImageEntry::make('image')->columnSpanFull(),
//                        ])->columns(2),
//                ])
//            ]);
//    }

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
