<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SalesType: string implements HasLabel, HasColor
{
    case Free = 'free';
    case Paid = 'paid';
    case Subscription = 'subscription';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Paid => 'Paid',
            self::Subscription => 'Subscription',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Free => Color::Green,
            self::Paid => Color::Amber,
            self::Subscription => Color::Purple,
        };
    }
}
