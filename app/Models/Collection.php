<?php

namespace App\Models;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Enums\SalesType;
use App\Traits\MoveToTop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Collection extends Model implements Sortable
{
    use HasFactory, SortableTrait, SoftDeletes, MoveToTop;

    protected $guarded = [];

    protected $casts = [
        'type' => PlatformType::class,
        'sales_type' => SalesType::class,
        'status' => RequestStatus::class,
        'is_active' => 'boolean',
        'is_banner' => 'boolean',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
    ];

    public function scopeActive(Builder $builder): void
    {
        $builder->where('is_active', true);
    }

    public function scopeFeatured(Builder $builder): void
    {
        $builder->where('is_featured', true);
    }

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

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
