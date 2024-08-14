<?php

namespace App\Filament\Resources\PayoutRequestResource\Pages;

use App\Enums\RequestStatus;
use App\Filament\Resources\PayoutRequestResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPayoutRequests extends ListRecords
{
    protected static string $resource = PayoutRequestResource::class;

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
