<?php

namespace App\Filament\Resources\SpecialRequestResource\Pages;

use App\Filament\Resources\SpecialRequestResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class SpecialRequestConversations extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SpecialRequestResource::class;

    protected static string $view = 'filament.resources.special-request-resource.pages.special-request-conversations';

    protected ?string $heading = 'Conversations';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
