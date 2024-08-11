<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RequestStatus: string implements HasLabel, HasColor
{
    use EnumFeatures;

    case Pending = 'pending';
    case Complete = 'complete';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'primary',
            self::Complete => 'success',
            self::Cancelled => 'danger',
        };
    }
}
