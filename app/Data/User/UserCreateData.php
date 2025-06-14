<?php

namespace App\Data\User;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class UserCreateData extends Data
{
    public string $name;

    public string $email;

    public string $password;

    public ?Carbon $email_verified_at;
}