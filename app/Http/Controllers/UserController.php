<?php

namespace App\Http\Controllers;

use App\Data\User\UserUpdateData;
use App\Data\User\UserUpdateEmailData;
use App\Data\User\UserUpdatePasswordData;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function me(): JsonResponse
    {
        Gate::authorize('view', $this->user);

        return $this->response(UserResource::make($this->user));
    }

    public function update(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $user = $userService->update(
            UserUpdateData::from([
                ...$request->toArray(),
                'user' => $this->user,
            ])
        );

        return UserResource::make($user)->response();
    }

    public function updateEmail(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', "not_in:{$this->user->email}", 'unique:users'],
        ]);

        $user = $userService->updateEmail(
            UserUpdateEmailData::from([
                ...$request->toArray(),
                'user' => $this->user,
            ])
        );

        return UserResource::make($user)->response();
    }

    public function updatePassword(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'old_password' => ['required', 'string', 'min:8', 'max:32', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'max:32', 'different:old_password', 'confirmed'],
        ]);

        $user = $userService->updatePassword(
            UserUpdatePasswordData::from([
                ...$request->toArray(),
                'user' => $this->user,
            ])
        );

        return UserResource::make($user)->response();
    }
}
