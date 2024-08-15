@vite('resources/css/app.css')
<x-filament-panels::page>
    <x-filament::section class="h-[75vh] overflow-auto">
        <div id="conversations">
            @for($i=0; $i<50; $i++)
               <div class="flex items-start gap-1">
                   <img class="rounded-full size-10" src="https://ui-avatars.com/api/?&length=1&name=User&size=64" alt=""/>
                   <div class="shadow p-2 bg-gray-400 rounded-lg inline-block">
                       Hi, how are you?
                   </div>
               </div>
                <div class="flex gap-1 justify-end">
                    <div class="shadow bg-primary-500 p-2 rounded-lg inline-block">
                        I'm good, how are you?
                    </div>
                    <img class="rounded-full size-10" src="https://ui-avatars.com/api/?&length=1&name=Admin&size=64&background=3b82f6" alt=""/>
                </div>
            @endfor
        </div>
    </x-filament::section>
</x-filament-panels::page>
@script
<script>

</script>
@endscript
