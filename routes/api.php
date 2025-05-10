<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::prefix('tracks')->group(function () {
    Route::get('', [TrackController::class, 'index']);
    Route::get('/active', [TrackController::class, 'getActive']);
    Route::post('', [TrackController::class, 'store']);
    Route::post('{track}', [TrackController::class, 'update']);
    Route::delete('{track}', [TrackController::class, 'destroy']);
});
