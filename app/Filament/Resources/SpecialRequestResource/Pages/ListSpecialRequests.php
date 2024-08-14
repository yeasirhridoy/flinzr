<?php

namespace App\Filament\Resources\SpecialRequestResource\Pages;

use App\Filament\Resources\SpecialRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialRequests extends ListRecords
{
    protected static string $resource = SpecialRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
