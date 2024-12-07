<?php

use App\Jobs\AddCoinsToInfluencer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new AddCoinsToInfluencer)->dailyAt('00:00');
