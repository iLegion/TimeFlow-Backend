<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNotificationsForUnverifiedUsers extends Command
{
    protected $signature = 'app:users:send-notifications-for-unverified-users';

    protected $description = 'Send notifications for unverified users';

    public function handle(): void
    {
        User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', today()->subDays(3))
            ->chunk(50, function (Collection $users) {
                $users->each(function (User $user) {
                    try {
                        Mail::to($user)->send(new \App\Mail\User\UserUnverifiedEmail($user));
                    } catch (Throwable $e) {
                        $this->error($e->getMessage());
                    }
                });
            });
    }
}
