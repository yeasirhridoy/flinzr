<?php

use App\Jobs\AddCoinsToInfluencer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::job(new AddCoinsToInfluencer)->dailyAt('00:00');
