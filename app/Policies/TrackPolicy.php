<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;

class TrackPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Track $track): bool
    {
        return $user->id === $track->user->id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Track $track): bool
    {
        return $user->id === $track->user->id;
    }

    public function delete(User $user, Track $track): bool
    {
        return $user->id === $track->user->id;
    }
}
