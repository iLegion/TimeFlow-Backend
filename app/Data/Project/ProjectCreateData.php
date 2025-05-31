<?php

namespace App\Data\Project;

use App\Models\User;
use Spatie\LaravelData\Data;

class ProjectCreateData extends Data
{
    public User $user;

    public string $title;
}
