<?php

namespace App\Services\Track;

use App\Data\Track\TrackCreateData;
use App\Data\Track\TrackUpdateData;
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

    public function start(TrackCreateData $data): Track
    {
        if ($activeTrack = $this->getActive($data->user))
            $this->update(
                $activeTrack,
                TrackUpdateData::from(['started_at' => $activeTrack->started_at, 'finished_at' => now()])
            );

        return $this->create($data);
    }

    public function create(TrackCreateData $data): Track
    {
        $track = new Track();
        $track->title = $data->title;
        $track->started_at = $data->started_at ?? now();
        $track->finished_at = $data->finished_at;

        $track->user()->associate($data->user);
        $track->save();

        return $track;
    }

    public function update(Track $track, TrackUpdateData $data): Track
    {
        if ($track->title !== $data->title) {
            $track->title = $data->title;
        }

        $track->started_at = $data->started_at ?? $track->started_at ?? now();
        $track->finished_at = $data->finished_at ?? $track->finished_at ?? now();

        if ($track->isDirty()) $track->save();

        return $track;
    }

    public function delete(Track $track): ?bool
    {
        return $track->delete();
    }
}
