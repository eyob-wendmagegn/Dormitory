<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated PostgreSQL backup & 30-day Google Drive retention cleanup every night at 2:00 AM
Schedule::command('db:backup --type=Automatic')
    ->dailyAt('02:00')
    ->withoutOverlapping();
