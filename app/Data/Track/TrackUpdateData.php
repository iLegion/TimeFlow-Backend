<?php

namespace App\Data\Track;

use App\Models\Project;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class TrackUpdateData extends Data
{
    public ?Project $project;

    public ?string $title = null;

    public ?Carbon $started_at = null;

    public ?Carbon $finished_at = null;
}
