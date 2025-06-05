<?php

namespace App\Data\Track;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class TrackCreateData extends Data
{
    public User $user;

    public ?Project $project;

    public ?string $title = null;

    public ?Carbon $started_at = null;

    public ?Carbon $finished_at = null;
}
