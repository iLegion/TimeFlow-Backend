<?php

use App\Console\Commands\User\DeleteUnverifiedUsers;
use App\Console\Commands\User\ForceDeleteUnverifiedUsers;
use App\Console\Commands\User\SendNotificationsForUnverifiedUsers;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(DeleteUnverifiedUsers::class)->weeklyOn(1);
Schedule::command(ForceDeleteUnverifiedUsers::class)->weeklyOn(1, '00:30');
Schedule::command(SendNotificationsForUnverifiedUsers::class)->weeklyOn(1, '01:00');
