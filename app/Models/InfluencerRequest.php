<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InfluencerRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        parent::creating(function ($model) {
            $model->request_number = strtoupper(Str::random(8));
        });
    }

    protected $casts = [
        'status' => RequestStatus::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
