<?php

namespace App\Services\Track;

use App\Data\Track\TrackCreateData;
use App\Data\Track\TrackIndexData;
use App\Data\Track\TrackUpdateData;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class TrackService
{
    public function get(TrackIndexData $data): Collection
    {
        return Track::query()
            ->with(['project'])
            ->whereNotNull('finished_at')
            ->when($data->from && $data->to, function ($query) use ($data) {
                $query
                    ->where('started_at', '>=', $data->from->startOfDay())
                    ->where('started_at', '<=', $data->to->endOfDay());
            })
            ->when(!$data->from || !$data->to, function ($query) use ($data) {
                $query->where('started_at', '>=', today()->subWeek());
            })
            ->whereBelongsTo($data->user)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function getActive(User $user): ?Track
    {
        return Track::query()
            ->with(['project'])
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

        if ($data->project) $track->project()->associate($data->project);

        $track->user()->associate($data->user);
        $track->save();

        return $track->load(['project']);
    }

    public function update(Track $track, TrackUpdateData $data): Track
    {
        if (!$data->title instanceof Optional && $track->title !== $data->title) {
            $track->title = $data->title;
        }

        if (!$data->project instanceof Optional) {
            if ($data->project) {
                if ($data->project->id !== $track->project?->id) {
                    $track->project()->associate($data->project);
                }
            } else {
                $track->project()->dissociate();
            }
        }

        if (!$data->started_at instanceof Optional) $track->started_at = $data->started_at;
        if (!$data->finished_at instanceof Optional) $track->finished_at = $data->finished_at;

        if ($track->isDirty()) $track->save();

        return $track->load(['project']);
    }

    public function delete(Track $track): ?bool
    {
        return $track->delete();
    }
}
