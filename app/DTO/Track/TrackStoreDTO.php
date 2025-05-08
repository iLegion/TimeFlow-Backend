<?php

namespace App\DTO\Track;

use Illuminate\Support\Carbon;

class TrackStoreDTO
{
    public function __construct(
        public ?string $title = null,
        public ?Carbon $started_at = null,
        public ?Carbon $finished_at = null,
    ) {}
}
