<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Contracts\HasLabel;

enum CommissionLevel: int implements HasLabel
{
    use EnumFeatures;

    case Level1 = 1;
    case Level2 = 2;
    case Level3 = 3;
    case Level4 = 4;
    case Level5 = 5;
    case Level6 = 6;
    case Level7 = 7;
    case Level8 = 8;

    public function getTarget(): int
    {
        return match ($this) {
            self::Level1 => 5,
            self::Level2 => 10,
            self::Level3 => 15,
            self::Level4 => 20,
            self::Level5 => 25,
            self::Level6 => 30,
            self::Level7 => 35,
            self::Level8 => 40,
        };
    }

    public function getCommission(): int
    {
        return match ($this) {
            self::Level1 => 10,
            self::Level2 => 15,
            self::Level3 => 20,
            self::Level4 => 25,
            self::Level5 => 30,
            self::Level6 => 35,
            self::Level7 => 40,
            self::Level8 => 45,
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Level1 => 'Level 1',
            self::Level2 => 'Level 2',
            self::Level3 => 'Level 3',
            self::Level4 => 'Level 4',
            self::Level5 => 'Level 5',
            self::Level6 => 'Level 6',
            self::Level7 => 'Level 7',
            self::Level8 => 'Level 8',
        };
    }
}
