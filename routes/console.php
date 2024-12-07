<?php

use App\Jobs\AddCoinsToInfluencer;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new AddCoinsToInfluencer)->dailyAt('00:00');
