<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
                    ->hiddenLabel()
                    ->rule('required'),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();
        $data['sender'] = 'admin';
        $this->dispatch('messageSent', $data);
        $this->form->fill();
    }
}
