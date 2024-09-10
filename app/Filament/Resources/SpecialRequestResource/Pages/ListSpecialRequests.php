<?php

namespace App\Filament\Resources\SpecialRequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Resources\SpecialRequestResource;
use App\Models\SpecialRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListSpecialRequests extends ListRecords
{
    protected static string $resource = SpecialRequestResource::class;

    public function getTabs(): array
    {
        return collect(array_merge(['all' => null], RequestStatus::all()))->mapWithKeys(function ($label, $status) {
            return [
                $status => $status == 'all' ? Tab::make('All')->badge(SpecialRequest::query()->count()) : Tab::make($label)
                    ->query(function ($query) use ($status) {
                        $query->where('status', $status);
                    })->badge(function () use ($status) {
                        return SpecialRequest::query()->where('status', $status)->count();
                    }),
            ];
        })->toArray();
    }
}
