<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SalesType: string implements HasLabel
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
}
