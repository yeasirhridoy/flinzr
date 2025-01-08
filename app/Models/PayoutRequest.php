<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

class PayoutRequest extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        parent::creating(function ($model) {
            $model->request_number = strtoupper(Str::random(8));
        });
    }

    protected $guarded = [];

    public function amount():Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100, set: fn($value) => $value * 100
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
