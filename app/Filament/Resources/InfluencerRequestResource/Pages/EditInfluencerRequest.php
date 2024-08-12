<?php

namespace App\Filament\Resources\InfluencerRequestResource\Pages;

use App\Filament\Resources\InfluencerRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInfluencerRequest extends EditRecord
{
    protected static string $resource = InfluencerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
