<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Models\Region;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return collect(array_merge(['all' => null], Region::query()->pluck('name','id')->toArray()))->mapWithKeys(function ($label, $region) {
            return [
                $region => $region == 'all' ? Tab::make('All') : Tab::make($label)
                    ->query(function ($query) use ($label) {
                        $query->whereHas('regions', function ($query) use ($label) {
                            $query->where('regions.name', $label);
                        });
                    }),
            ];
        })->toArray();
    }
}
