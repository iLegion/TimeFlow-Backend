<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function delete(): bool
    {
        return false;
    }
}
