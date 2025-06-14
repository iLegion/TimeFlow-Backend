<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class ForceDeleteUnverifiedUsers extends Command
{
    protected $signature = 'app:users:force-delete-unverified-users';

    protected $description = 'Force delete users who have not verified their email after one month of registration';

    public function handle(): void
    {
        User::onlyTrashed()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', today()->subMonth()->addWeeks(2))
            ->chunk(50, function (Collection $users) {
                $users->each(function (User $user) {
                    try {
                        $user->forceDelete();
                    } catch (Throwable $e) {
                        $this->error($e->getMessage());
                    }
                });
            });
    }
}
