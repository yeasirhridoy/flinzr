<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\SortableTrait;

class Region extends Model
{
    use HasFactory, SortableTrait;

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
