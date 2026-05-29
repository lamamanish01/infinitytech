<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('customers:clean-stale-sessions')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('customers:update-expired')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('customers:bind-mac')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
