<?php

namespace App\Filament\Resources\SpecialRequestResource\Pages;

use App\Filament\Resources\SpecialRequestResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class SpecialRequestConversations extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SpecialRequestResource::class;

    protected static string $view = 'filament.resources.special-request-resource.pages.special-request-conversations';

    protected ?string $heading = 'Conversations';

    public Collection $conversations;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->conversations = $this->record->conversations;
    }
}
