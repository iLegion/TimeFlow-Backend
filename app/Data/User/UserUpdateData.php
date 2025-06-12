<?php

namespace App\Data\User;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserUpdateData extends Data
{
    public User $user;

    public ?string $name;
}