<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthSocialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::prefix('auth')->middleware(['throttle:auth'])->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::prefix('social')->group(function () {
            Route::prefix('google')->group(function () {
                Route::get('redirect', [AuthSocialController::class, 'redirectGoogle']);
                Route::get('callback', [AuthSocialController::class, 'callbackGoogle']);
            });
            Route::prefix('github')->group(function () {
                Route::get('redirect', [AuthSocialController::class, 'redirectGithub']);
                Route::get('callback', [AuthSocialController::class, 'callbackGithub']);
            });
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('auth')->middleware(['throttle:auth'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('users')->group(function () {
            Route::prefix('me')->group(function () {
                Route::get('', [UserController::class, 'me']);
                Route::post('', [UserController::class, 'update']);
                Route::post('email', [UserController::class, 'updateEmail']);
                Route::post('password', [UserController::class, 'updatePassword']);
            });
        });

        Route::prefix('email-verification')->group(function () {
            Route::post('send', [UserEmailVerificationController::class, 'send']);
            Route::post('verify', [UserEmailVerificationController::class, 'verify']);
        });

        Route::prefix('projects')->group(function () {
            Route::get('', [ProjectController::class, 'index']);
            Route::post('', [ProjectController::class, 'store']);
            Route::post('{project}', [ProjectController::class, 'update']);
            Route::delete('{project}', [ProjectController::class, 'delete']);
        });

        Route::prefix('tracks')->group(function () {
            Route::get('', [TrackController::class, 'index']);
            Route::get('active', [TrackController::class, 'getActive']);
            Route::post('', [TrackController::class, 'store']);
            Route::post('{track}', [TrackController::class, 'update']);
            Route::delete('{track}', [TrackController::class, 'destroy']);
        });
    });
});
