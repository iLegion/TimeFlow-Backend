<?php

namespace App\Data\SocialProvider;

use App\Models\User;
use Spatie\LaravelData\Data;

class SocialProviderCreateData extends Data
{
    public User $user;

    public string $provider_id;

    public string $provider_name;
}