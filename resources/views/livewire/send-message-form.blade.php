<div>
    <form wire:submit="sendMessage" class="flex gap-1">
        <div class="flex-grow">
            {{ $this->form }}
        </div>

       <div>
           <x-filament::button type="submit">
               Send
           </x-filament::button>
       </div>
    </form>
</div>
