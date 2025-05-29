<?php

namespace App\DTO\Track;

use App\Models\User;
use Illuminate\Support\Carbon;

class TrackCreateDTO
{
    public function __construct(
        public User $user,
        public ?string $title = null,
        public ?Carbon $started_at = null,
        public ?Carbon $finished_at = null,
    ) {}
}
