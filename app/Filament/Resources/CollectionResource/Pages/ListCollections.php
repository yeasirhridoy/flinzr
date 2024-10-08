<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Enums\PlatformType;
use App\Filament\Resources\CollectionResource;
use App\Models\Collection;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return collect(array_merge(['all' => null], PlatformType::all()))->mapWithKeys(function ($label, $type) {
            return [
                $type => $type == 'all' ? Tab::make('All')->badge(Collection::query()->count()) : Tab::make($label)
                    ->query(function ($query) use ($type) {
                        $query->where('type', $type);
                    })->badge(function () use ($type) {
                        return Collection::query()->where('type', $type)->count();
                    }),
            ];
        })->toArray();
    }
}
