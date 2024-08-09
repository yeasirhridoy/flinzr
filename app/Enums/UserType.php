<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasLabel, HasColor
{
    use EnumFeatures;

    case Customer = 'customer';
    case Artist = 'artist';
    case Influencer = 'influencer';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Customer => Color::Blue,
            self::Artist => Color::Green,
            self::Influencer => Color::Purple,
        };
    }
}
