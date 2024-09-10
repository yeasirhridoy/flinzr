<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100, set: fn($value) => $value * 100
        );
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }
}
