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

    public function getListeners(): array
    {
        return [
            "echo:conversation.{$this->specialRequest->id},MessageSent" => 'addConversation',
        ];
    }

    public function fetchNewConversations(): void
    {
        $newConversation = $this->specialRequest->conversations()->where('id', '>', $this->conversations->last()->id)->get();
        foreach ($newConversation as $conversation) {
            $this->conversations->push($conversation);
            $this->dispatch('conversationAdded');
        }
    }

    public function addConversation($event): void
    {
        info('listening');
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
