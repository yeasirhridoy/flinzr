<?php

namespace App\Models;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => RequestStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
