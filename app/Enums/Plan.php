<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Contracts\HasLabel;

enum Plan: string implements HasLabel
{
    use EnumFeatures;
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function getFeatures(): array
    {
        return match ($this) {
            self::Monthly => [
                '9 Plus Filters' => true,
                '9 Paid Filters' => true,
                '9 Gifts To Friend' => true,
                '50% Special Order' => false,
                '2 Free Coins Daily' => false,
                'No More Ads' => true
            ],
            self::Yearly => [
                '9 Plus Filters' => true,
                '9 Paid Filters' => true,
                '9 Gifts To Friend' => true,
                '50% Special Order' => true,
                '2 Free Coins Daily' => true,
                'No More Ads' => true
            ],
        };
    }

    public function getPrice(): float
    {
        return match ($this) {
            self::Monthly => 19.99,
            self::Yearly => 119.99,
        };
    }
}
