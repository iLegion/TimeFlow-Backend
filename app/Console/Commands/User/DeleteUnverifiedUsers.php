<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class DeleteUnverifiedUsers extends Command
{
    protected $signature = 'app:users:delete-unverified-users';

    protected $description = 'Delete users who have not verified their email after one month of registration';

    public function handle(): void
    {
        User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', today()->subMonth())
            ->chunk(50, function (Collection $users) {
                $users->each(function (User $user) {
                    try {
                        $user->delete();
                    } catch (Throwable $e) {
                        $this->error($e->getMessage());
                    }
                });
            });
    }
}
