<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CommissionLevel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.commission-level';

    protected static ?string $navigationGroup = 'Settings';
}
