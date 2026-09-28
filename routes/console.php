<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule expiration product/batch check to run daily at 6 AM
Schedule::command('check:batch-expiry')->daily()->at('06:00');

// Schedule notification cleanup to run daily at 3 AM
Schedule::command('notifications:cleanup')->daily()->at('03:00');
