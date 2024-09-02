<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function earning(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100, set: fn($value) => $value * 100
        );
    }
}
