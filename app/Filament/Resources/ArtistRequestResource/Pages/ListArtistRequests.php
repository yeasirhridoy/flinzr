<?php

namespace App\Filament\Resources\ArtistRequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Resources\ArtistRequestResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
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

    public function getTabs(): array
    {
        return collect(array_merge(['all' => null], RequestStatus::all()))->mapWithKeys(function ($label, $status) {
            return [
                $status => $status == 'all' ? Tab::make('All') : Tab::make($label)
                    ->query(function ($query) use ($status) {
                        $query->where('status', $status);
                    }),
            ];
        })->toArray();
    }
}
