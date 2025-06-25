<?php

namespace App\Data\Track;

use App\Models\Project;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TrackUpdateData extends Data
{
    public Project | null | Optional $project;

    public string | null | Optional $title;

    public Carbon | Optional $started_at;

    public Carbon | Optional $finished_at;
}
