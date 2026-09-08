<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:expire --hours=24')->hourly();
Schedule::command('accommodations:expire --hours=24')->hourly();
Schedule::command('accommodations:sync --limit=20')->dailyAt('02:00');
