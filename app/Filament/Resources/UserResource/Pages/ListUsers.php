<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserType;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return collect(array_merge(['all' => null], UserType::all()))->mapWithKeys(function ($label, $type) {
            return [
                $type => $type == 'all' ? Tab::make('All')->badge(User::query()->whereNot('email', 'devoartsa@gmail.com')->count()) : Tab::make($label)
                    ->query(function ($query) use ($type) {
                        $query->where('type', $type);
                    })->badge(function () use ($type) {
                        return User::query()->where('type', $type)->whereNot('email', 'devoartsa@gmail.com')->count();
                    }),
            ];
        })->toArray();
    }
}
