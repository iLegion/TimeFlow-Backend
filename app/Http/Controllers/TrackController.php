<?php

namespace App\Http\Controllers;

use App\DTO\Track\TrackCreateDTO;
use App\DTO\Track\TrackUpdateDTO;
use App\Http\Resources\Track\TrackResource;
use App\Models\Track;
use App\Services\Track\TrackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class TrackController extends Controller
{
    public function index(TrackService $service): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Track::class);

        return TrackResource::collection($service->get($this->user));
    }

    public function getActive(TrackService $service): TrackResource | JsonResponse
    {
        $track = $service->getActive($this->user);

        if ($track) {
            Gate::authorize('view', $track);

            return TrackResource::make($track);
        }

        return response()->json(['data' => null]);
    }

    public function store(Request $request, TrackService $service): JsonResponse
    {
        Gate::authorize('create', Track::class);

        $request->validate([
            'title' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
            'finished_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
        ]);

        $track = $service->start(
            new TrackCreateDTO(
                $this->user,
                $request->input('title'),
                $request->exists('started_at') ? Carbon::parse($request->input('started_at')) : null,
                $request->exists('finished_at') ? Carbon::parse($request->input('finished_at')) : null,
            )
        );

        return TrackResource::make($track)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Track $track, TrackService $service): JsonResponse
    {
        Gate::authorize('update', $track);

        $request->validate([
            'title' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
            'finished_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
        ]);

        $track = $service->update(
            $track,
            new TrackUpdateDTO(
                $request->input('title'),
                $request->exists('started_at') ? Carbon::parse($request->input('started_at')) : null,
                $request->exists('finished_at') ? Carbon::parse($request->input('finished_at')) : null,
            )
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
