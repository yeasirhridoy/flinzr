<?php

namespace App\Traits;

trait EnumFeatures
{
    public static function all(): array
    {
        $items = [];

        foreach (self::cases() as $case) {
            $items[$case->value] = $case->name;
        }

        return $items;
    }

    public static function values(): array
    {
        return array_map(fn ($value) => $value->value, self::cases());
    }

    public static function names(): array
    {
        return array_map(fn ($value) => $value->name, self::cases());
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
