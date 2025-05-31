<?php

namespace App\Http\Controllers;

use App\Data\Project\ProjectCreateData;
use App\Data\Project\ProjectUpdateData;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(ProjectService $service): JsonResponse
    {
        Gate::authorize('viewAny', Project::class);

        return ProjectResource::collection($service->get($this->user))->response();
    }

    public function store(Request $request, ProjectService $service): JsonResponse
    {
        Gate::authorize('create', Project::class);

        $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $project = $service->create(
            ProjectCreateData::from([
                ...$request->toArray(),
                'user' => $this->user,
            ])
        );

        return ProjectResource::make($project)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Project $project, ProjectService $service): JsonResponse
    {
        Gate::authorize('update', $project);

        $request->validate([
            'title' => ['sometimes', 'required', 'string', 'min:1', 'max:1000'],
        ]);

        $project = $service->update($project, ProjectUpdateData::from($request->toArray()));

        return ProjectResource::make($project)->response();
    }

    public function delete(Project $project, ProjectService $service): JsonResponse
    {
        Gate::authorize('delete', $project);

        $service->delete($project);

        return response()->json();
    }
}
