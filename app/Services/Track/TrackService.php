<?php

namespace App\Services\Track;

use App\DTO\Track\TrackCreateDTO;
use App\DTO\Track\TrackUpdateDTO;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TrackService
{
    public function get(User $user): Collection
    {
        return Track::query()
            ->whereNotNull('finished_at')
            ->where('started_at', '>=', now()->subWeek())
            ->whereBelongsTo($user)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function getActive(User $user): ?Track
    {
        return Track::query()
            ->whereNull('finished_at')
            ->whereBelongsTo($user)
            ->first();
    }

    public function start(TrackCreateDTO $dto): Track
    {
        if ($activeTrack = $this->getActive($dto->user))
            $this->update($activeTrack, new TrackUpdateDTO(started_at: $activeTrack->started_at, finished_at: now()));

        return $this->create($dto);
    }

    public function create(TrackCreateDTO $dto): Track
    {
        $track = new Track();
        $track->title = $dto->title;
        $track->started_at = $dto->started_at ?? now();
        $track->finished_at = $dto->finished_at;

        $track->user()->associate($dto->user);
        $track->save();

        return $track;
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
