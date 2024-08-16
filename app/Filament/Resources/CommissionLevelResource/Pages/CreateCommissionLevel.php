<?php

namespace App\Filament\Resources\CommissionLevelResource\Pages;

use App\Filament\Resources\CommissionLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCommissionLevel extends CreateRecord
{
    protected static string $resource = CommissionLevelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
