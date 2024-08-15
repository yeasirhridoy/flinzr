@vite('resources/css/app.css')
<x-filament-panels::page>
    <x-filament::section class="h-[75vh] overflow-auto">
        <div id="conversations">
            @foreach($conversations as $conversation)
               <div @class(["flex my-1 items-start gap-1", "flex-row-reverse"=>$conversation->sender==='admin'])>
                   <img class="rounded-full size-10" src="https://ui-avatars.com/api/?&length=1&name={{$conversation->sender}}&size=64" alt=""/>
                   <div @class(["shadow p-2 rounded-lg inline-block", "bg-gray-400"=> $conversation->sender === 'user', "bg-primary-500"=> $conversation->sender === 'admin'])>
                       {{$conversation->message}}
                   </div>
               </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
@script
<script>

</script>
@endscript
