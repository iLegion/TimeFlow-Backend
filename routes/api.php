<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::prefix('track')->group(function () {
    Route::get('', [TrackController::class, 'index']);
    Route::post('', [TrackController::class, 'store']);
    Route::post('{track}', [TrackController::class, 'update']);
    Route::delete('{track}', [TrackController::class, 'destroy']);
});
