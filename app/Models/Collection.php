<?php

namespace App\Models;

use App\Enums\PlatformType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        parent::creating(function (Collection $collection) {
            $collection->user_id = auth()->id();
        });
    }

    protected $casts = [
        'type' => PlatformType::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class)->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class)->withTimestamps();
    }

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }
}
