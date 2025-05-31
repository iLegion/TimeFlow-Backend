<?php

namespace App\Services\Project;

use App\Data\Project\ProjectCreateData;
use App\Data\Project\ProjectUpdateData;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * @return Collection<int, User>
     */
    public function get(User $user): Collection
    {
        return Project::query()
            ->whereBelongsTo($user)
            ->get();
    }

    public function create(ProjectCreateData $data): Project
    {
        $project = new Project();
        $project->title = $data->title;

        $project->user()->associate($data->user);
        $project->save();

        return $project;
    }

    public function update(Project $project, ProjectUpdateData $data): Project
    {
        if ($project->title !== $data->title) {
            $project->title = $data->title;
        }

        if ($project->isDirty()) $project->save();

        return $project;
    }

    public function delete(Project $project): ?bool
    {
        return $project->delete();
    }
}
