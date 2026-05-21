<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Payment reminders
Schedule::command('payments:send-reminders')->dailyAt('06:00');
Schedule::command('payments:send-reminders')->dailyAt('09:00');

// Daily tasks
Schedule::command('emails:send')->daily();
Schedule::command('backup:database')->dailyAt('02:00');

// Hourly tasks
Schedule::call(function () {
    \App\Models\Log::create(['message' => 'Hourly task executed']);
})->hourly();

// Conditional schedules
Schedule::command('reports:generate')
    ->weekdays()
    ->at('08:00')
    ->when(function () {
        return today()->isWeekday();
});

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
