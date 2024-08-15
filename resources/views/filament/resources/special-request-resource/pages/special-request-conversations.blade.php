@vite('resources/css/app.css')
<x-filament-panels::page>
    <x-filament::section>
        <livewire:special-conversations :special-request="$this->getRecord()"/>
        <livewire:send-message-form/>
        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-panels::page>
@script
<script>

</script>
@endscript
