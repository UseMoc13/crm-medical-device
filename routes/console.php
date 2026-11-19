<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// A7.4.11.1: always register the reminder command before scheduling it.
\Illuminate\Console\Application::starting(static function ($artisan): void {
    if (!$artisan->has('workspace:reminders:sync')) $artisan->resolve(\App\Console\Commands\SyncWorkspaceReminders::class);
});
// Call `php artisan schedule:run` every minute via Windows Task Scheduler/cron.
Schedule::command('workspace:reminders:sync')->everyFifteenMinutes()->withoutOverlapping();

// A7.6.4: scheduled publishing is a WRITE; never run it from Dashboard Blade/GET.
\Illuminate\Console\Application::starting(static function ($artisan): void {
    if (!$artisan->has('crm:announcements:publish-due')) $artisan->resolve(\App\Console\Commands\PublishDueCrmAnnouncements::class);
});
Schedule::command('crm:announcements:publish-due')->everyMinute()->withoutOverlapping();
