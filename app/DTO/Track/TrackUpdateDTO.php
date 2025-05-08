<?php

namespace App\DTO\Track;

use Illuminate\Support\Carbon;

class TrackUpdateDTO
{
    public function __construct(
        public ?string $title = null,
        public ?Carbon $started_at = null,
        public ?Carbon $finished_at = null,
    ) {}
}
