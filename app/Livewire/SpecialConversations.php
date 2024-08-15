<?php

namespace App\Livewire;

use App\Models\SpecialRequest;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class SpecialConversations extends Component
{
    public Collection $conversations;
    public SpecialRequest $specialRequest;

    public function mount(): void
    {
        $this->conversations = $this->specialRequest->conversations;
    }

    #[On('messageSent')]
    public function sendMessage(array $data): void
    {
        $conversation = $this->specialRequest->conversations()->create($data);
        $this->conversations->push($conversation);
    }

    public function render()
    {
        return view('livewire.special-conversations');
    }
}
