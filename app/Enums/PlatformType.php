<?php

namespace App\Enums;

use App\Traits\EnumFeatures;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PlatformType: string implements HasLabel, HasColor, HasIcon
{
    use EnumFeatures;
    case Banner = 'banner';
    case Snapchat = 'snapchat';
    case Tiktok = 'tiktok';
    case Instagram = 'instagram';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Banner => Color::Gray,
            self::Snapchat => Color::Yellow,
            self::Tiktok => Color::Purple,
            self::Instagram => Color::Pink,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Banner => 'icon-banner',
            self::Snapchat => 'icon-snapchat',
            self::Tiktok => 'icon-tiktok',
            self::Instagram => 'icon-instagram',
        };
    }
}
