<?php

namespace App\Http\Controllers;

use App\Data\Track\TrackCreateData;
use App\Data\Track\TrackIndexData;
use App\Data\Track\TrackUpdateData;
use App\Http\Resources\Track\TrackResource;
use App\Models\Project;
use App\Models\Track;
use App\Services\Track\TrackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class TrackController extends Controller
{
    public function index(Request $request, TrackService $service): JsonResponse
    {
        Gate::authorize('viewAny', Track::class);

        $request->validate([
            'from' => ['sometimes', 'required_with:to', 'exclude_without:to', Rule::date()->format('Y-m-d')],
            'to' => ['sometimes', 'required_with:from', 'exclude_without:from', Rule::date()->format('Y-m-d')],
        ]);

        $tracks = $service->get(
            TrackIndexData::from([
                ...$request->toArray(),
                'from' => $request->exists('from') ? Carbon::parse($request->input('from')) : null,
                'to' => $request->exists('to') ? Carbon::parse($request->input('to')) : null,
                'user' => $this->user,
            ])
        );

        return TrackResource::collection($tracks)->response();
    }

    public function getActive(TrackService $service): JsonResponse
    {
        $track = $service->getActive($this->user);

        if ($track) {
            Gate::authorize('view', $track);

            return TrackResource::make($track)->response();
        }

        return response()->json(['data' => null]);
    }

    public function store(Request $request, TrackService $service): JsonResponse
    {
        Gate::authorize('create', Track::class);

        $request->validate([
            'project_id' => ['sometimes', 'required', 'int', 'exists:projects,id'],
            'title' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
            'finished_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
        ]);

        $track = $service->start(
            TrackCreateData::from([
                ...$request->toArray(),
                'started_at' => $request->exists('started_at') ? Carbon::parse($request->input('started_at')) : null,
                'finished_at' => $request->exists('finished_at') ? Carbon::parse($request->input('finished_at')) : null,
                'user' => $this->user,
                'project' => Project::query()->find($request->input('project_id')),
            ])
        );

        return TrackResource::make($track)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Track $track, TrackService $service): JsonResponse
    {
        Gate::authorize('update', $track);

        $request->validate([
            'project_id' => ['sometimes', 'required', 'int', 'exists:projects,id'],
            'title' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
            'finished_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
        ]);

        $track = $service->update(
            $track,
            TrackUpdateData::from([
                ...$request->toArray(),
                'started_at' => $request->exists('started_at') ? Carbon::parse($request->input('started_at')) : null,
                'finished_at' => $request->exists('finished_at') ? Carbon::parse($request->input('finished_at')) : null,
                'project' => Project::query()->find($request->input('project_id')),
            ])
        );

        return TrackResource::make($track)->response();
    }

    public function destroy(Track $track, TrackService $service): JsonResponse
    {
        Gate::authorize('delete', $track);

        $service->delete($track);

        return response()->json();
    }
}
