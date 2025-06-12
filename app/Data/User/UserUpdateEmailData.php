<?php

namespace App\Data\User;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserUpdateEmailData extends Data
{
    public User $user;

    public string $email;
}