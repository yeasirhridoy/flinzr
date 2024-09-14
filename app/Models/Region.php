<?php

namespace App\Models;

use App\Traits\MoveToTop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Region extends Model implements Sortable
{
    use HasFactory, SortableTrait, MoveToTop;

    protected $guarded = [];

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }
}
