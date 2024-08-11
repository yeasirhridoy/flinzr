<?php

namespace App\Filament\Resources\ArtistRequestResource\Pages;

use App\Filament\Resources\ArtistRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtistRequests extends ListRecords
{
    protected static string $resource = ArtistRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
