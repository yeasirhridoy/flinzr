<?php

namespace App\Filament\Resources\CommissionLevelResource\Pages;

use App\Filament\Resources\CommissionLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommissionLevels extends ListRecords
{
    protected static string $resource = CommissionLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
