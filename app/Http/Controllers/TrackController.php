<?php

namespace App\Http\Controllers;

use App\DTO\Track\TrackStoreDTO;
use App\DTO\Track\TrackUpdateDTO;
use App\Http\Resources\Track\TrackResource;
use App\Models\Track;
use App\Services\Track\TrackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TrackController extends Controller
{
    public function index(TrackService $service): AnonymousResourceCollection
    {
        return TrackResource::collection($service->get());
    }

    public function getActive(TrackService $service): TrackResource
    {
        return TrackResource::make($service->getActive());
    }

    public function store(Request $request, TrackService $service): TrackResource
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
            'finished_at' => ['nullable', Rule::date()->format('Y-m-d H:i:s')],
        ]);

        $track = $service->start(
            new TrackStoreDTO(
                $request->input('title'),
                $request->exists('started_at') ? Carbon::parse($request->input('started_at')) : null,
                $request->exists('finished_at') ? Carbon::parse($request->input('finished_at')) : null,
            )
        );

        return TrackResource::make($track);
    }

    public function update(Request $request, Track $track, TrackService $service): TrackResource
    {
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

        return TrackResource::make($track);
    }

    public function destroy(Track $track, TrackService $service): JsonResponse
    {
        $service->delete($track);

        return response()->json();
    }
}
