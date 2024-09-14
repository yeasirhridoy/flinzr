<?php

namespace App\Models;

use App\Traits\MoveToTop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Country extends Model implements Sortable
{
    use HasFactory, SortableTrait, MoveToTop;

    protected $guarded = [];

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class)->withTimestamps();
    }
}
