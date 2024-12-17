<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class SendMessageForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('message')
                    ->autofocus()
                    ->hiddenLabel()
                    ->requiredWithout('attachments'),
                FileUpload::make('attachments')
                    ->label('Attachments')
                    ->requiredWithout('message')
                    ->hiddenLabel()
                    ->multiple()
                    ->image()
                    ->maxFiles(5),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();

        if (!empty($data['attachments'])) {
            $data['attachments'] = Storage::url($data['attachments']);
        }

        $data['sender'] = 'admin';
        $this->dispatch('messageSent', $data);
        $this->form->fill();
    }
}
