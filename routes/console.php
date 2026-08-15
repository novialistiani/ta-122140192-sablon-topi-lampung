<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check expired VAs every minute (handles: stock restore, order cancel, payment status reset)
Schedule::command('va:check-expired')->everyMinute();

// Note: va:expire is deprecated and now just calls va:check-expired
