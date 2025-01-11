<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Contracts\HasLabel;

enum Price: string implements HasLabel
{
    use EnumFeatures;

    case Filter = 'filter';
    case SpecialFilter = 'special_filter';
    case GiftFilter = 'gift_filter';

    public function getPrice(): int
    {
        return match ($this) {
            self::Filter => 25,
            self::SpecialFilter => 600,
            self::GiftFilter => 10,
        };
    }
}
