<?php

namespace App\Filament\Resources\ArtistRequestResource\Pages;

use App\Filament\Resources\ArtistRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArtistRequest extends CreateRecord
{
    protected static string $resource = ArtistRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return parent::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
        ];
    }
}
