<?php

namespace App\Filament\Resources\PayoutMethodResource\Pages;

use App\Filament\Resources\PayoutMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayoutMethod extends CreateRecord
{
    protected static string $resource = PayoutMethodResource::class;

    protected static bool $canCreateAnother = true;
}
