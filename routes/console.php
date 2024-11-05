<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;  
use Illuminate\Support\Facades\Schedule;
  
Schedule::command('sample:test')->everyFiveMinutes();
Schedule::command('yearly:fl')->everyFiveMinutes();
Schedule::command('monthly:cardview')->monthly();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
