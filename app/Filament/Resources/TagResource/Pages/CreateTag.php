<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
        ];
    }

    protected function getRedirectUrl(): string
    {
        return parent::getResource()::getUrl('index');
    }
}
