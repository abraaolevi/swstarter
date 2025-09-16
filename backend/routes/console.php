<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CalculateStatsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('stats:calculate', function () {
    $this->info('Dispatching the job...');
    CalculateStatsJob::dispatch();
    $this->info('Job CalculateStatsJob dispatched successfully!');
})->purpose('Calculate search statistics and update cache');

Schedule::job(new CalculateStatsJob)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
