<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Contracts\HasLabel;

enum PlatformType: string implements HasLabel
{
    use EnumFeatures;
    case Banner = 'banner';
    case Snapchat = 'snapchat';
    case Tiktok = 'tiktok';
    case Instagram = 'instagram';
}
