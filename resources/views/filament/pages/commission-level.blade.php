<x-filament-panels::page>
    <div style="display: grid;grid-template-columns: repeat(4,minmax(0,1fr));gap: 1rem">
        @foreach(\App\Enums\CommissionLevel::cases() as $commissionLevel)
            <div>
                <x-filament::fieldset>
                    <x-slot name="label">
                        {{ $commissionLevel->getLabel() }}
                    </x-slot>
                    <div>
                        <div>
                            Target: {{ $commissionLevel->getTarget() }}
                        </div>
                        <div>
                            Percentage: {{ $commissionLevel->getCommission() }}%
                        </div>
                    </div>
                </x-filament::fieldset>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
