<?php

namespace App\Filament\Resources\ArtistRequestResource\Pages;

use App\Filament\Resources\ArtistRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtistRequest extends EditRecord
{
    protected static string $resource = ArtistRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return parent::getResource()::getUrl('index');
    }
}
