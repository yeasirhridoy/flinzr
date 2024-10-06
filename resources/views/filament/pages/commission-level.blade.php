@php use App\Enums\CommissionLevel; @endphp
<x-filament-panels::page>
    <div class="grid level-card gap-4">
        @foreach(CommissionLevel::cases() as $commissionLevel)
            <div class="col-span-1">
                <x-filament::section>
                    <x-slot name="heading">
                        {{ $commissionLevel->getLabel() }}
                    </x-slot>
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
                </x-filament::section>
            </div>
        @endforeach
    </div>
    <style>
        .level-card {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width: 768px) {
            .level-card {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
</x-filament-panels::page>
