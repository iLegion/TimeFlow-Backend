<?php

namespace App\Data\User;

use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class UserUpdateData extends Data
{
    public User $user;

    public ?string $name;

    public ?string $email;

    public ?string $password;

    public ?Carbon $email_verified_at;
}