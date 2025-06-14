<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function forceDeleting(User $user): void
    {
        $user->tracks()->forceDelete();
        $user->projects()->forceDelete();
    }
}
