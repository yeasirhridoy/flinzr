<?php

namespace App\Filament\Resources\PayoutMethodResource\Pages;

use App\Filament\Resources\PayoutMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayoutMethods extends ListRecords
{
    protected static string $resource = PayoutMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
