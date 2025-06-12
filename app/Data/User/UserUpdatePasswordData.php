<?php

namespace App\Data\User;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserUpdatePasswordData extends Data
{
    public User $user;

    public string $new_password;
}