<?php

namespace App\Http\Resources\Track;

use App\Http\Resources\Project\ProjectResource;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Track
 */
class TrackResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'project' => $this->relationLoaded('project') ? ProjectResource::make($this->project) : null,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
        ];
    }
}
