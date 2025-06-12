<?php

namespace App\Data\Track;

use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class TrackIndexData extends Data
{
    public User $user;

    public ?Carbon $from = null;

    public ?Carbon $to = null;
}
