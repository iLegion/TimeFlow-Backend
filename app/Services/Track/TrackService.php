<?php

namespace App\Services\Track;

use App\DTO\Track\TrackStoreDTO;
use App\DTO\Track\TrackUpdateDTO;
use App\Models\Track;
use Illuminate\Database\Eloquent\Collection;

class TrackService
{
    public function get(): Collection
    {
        return Track::query()
            ->whereNotNull('finished_at')
            ->where('started_at', '>=', now()->subWeek())
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function getActive(): ?Track
    {
        return Track::query()->whereNull('finished_at')->first();
    }

    public function start(TrackStoreDTO $dto): Track
    {
        if ($activeTrack = $this->getActive())
            $this->update($activeTrack, new TrackUpdateDTO(started_at: $activeTrack->started_at, finished_at: now()));

        return $this->create($dto);
    }

    public function create(TrackStoreDTO $dto): Track
    {
        return Track::query()->create([
            'title' => $dto->title,
            'started_at' => $dto->started_at ?? now(),
            'finished_at' => $dto->finished_at,
        ]);
    }

    public function update(Track $track, TrackUpdateDTO $dto): Track
    {
        $track->update([
            'title' => $dto->title ?? $track->title,
            'started_at' => $dto->started_at ?? $track->started_at ?? now(),
            'finished_at' => $dto->finished_at ?? $track->finished_at ?? now(),
        ]);

        return $track;
    }

    public function delete(Track $track): ?bool
    {
        return $track->delete();
    }
}
