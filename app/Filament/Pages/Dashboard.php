<?php

namespace App\Filament\Pages;

use App\Enums\PlatformType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('type')
                            ->label('Select Platform')
                            ->placeholder('All')
                            ->options([
                                PlatformType::Snapchat->value => PlatformType::Snapchat->name,
                                PlatformType::Instagram->value => PlatformType::Instagram->name,
                                PlatformType::Tiktok->value => PlatformType::Tiktok->name,
                            ]),
                        DatePicker::make('start_date'),
                        DatePicker::make('end_date')
                            ->after('start_date'),
                    ])
                    ->columns(3),
            ]);
    }
}
