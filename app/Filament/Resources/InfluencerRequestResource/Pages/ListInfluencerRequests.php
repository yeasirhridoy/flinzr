<?php

namespace App\Filament\Resources\InfluencerRequestResource\Pages;

use App\Filament\Resources\InfluencerRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfluencerRequests extends ListRecords
{
    protected static string $resource = InfluencerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
