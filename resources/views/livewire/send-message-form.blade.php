<div>
    <form wire:submit="sendMessage">
        <div class="flex-grow mb-4">
            {{ $this->form }}
        </div>

       <div>
           <x-filament::button type="submit" class="w-full">
               Send
           </x-filament::button>
       </div>
    </form>
</div>
