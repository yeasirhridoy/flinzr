<?php

namespace App\Traits;

trait MoveToTop
{
    protected static function bootMoveToTop(): void
    {
        static::created(function ($model) {
            $model->moveToStart();
        });
    }
}
