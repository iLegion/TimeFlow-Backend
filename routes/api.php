<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::prefix('auth')->middleware(['throttle:auth'])->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('auth')->middleware(['throttle:auth'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('users')->group(function () {
            Route::get('me', [UserController::class, 'me']);
        });

        Route::prefix('tracks')->group(function () {
            Route::get('', [TrackController::class, 'index']);
            Route::get('/active', [TrackController::class, 'getActive']);
            Route::post('', [TrackController::class, 'store']);
            Route::post('{track}', [TrackController::class, 'update']);
            Route::delete('{track}', [TrackController::class, 'destroy']);
        });
    });
});
