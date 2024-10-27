<?php

namespace App\Livewire;

use App\Events\MessageSent;
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

//    protected $listeners = ["echo:conversation,.MessageSent" => 'addConversation'];

//    public function getListeners(): array
//    {
//        return [
//            "echo:conversation,.".MessageSent::class => 'addConversation',
//        ];
//    }

    #[On('echo:conversation,MessageSent')]
    public function addConversation($event): void
    {
        info($event);
        $this->conversations->push($event['conversation']);
        $this->dispatch('conversationAdded');
    }

    #[On('messageSent')]
    public function sendMessage(array $data): void
    {
        $conversation = $this->specialRequest->conversations()->create($data);
        $this->conversations->push($conversation);
        $this->dispatch('conversationAdded');
        MessageSent::dispatch($conversation);
    }
}
