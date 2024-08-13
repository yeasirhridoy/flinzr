<?php

namespace App\Models;

use App\Enums\PlatformType;
use App\Enums\SalesType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Collection extends Model implements Sortable
{
    use HasFactory, SortableTrait;

    protected $guarded = [];

    protected $casts = [
        'type' => PlatformType::class,
        'sales_type' => SalesType::class,
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

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class)->withTimestamps();
    }

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }
}
