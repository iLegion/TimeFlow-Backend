<?php

namespace App\Http\Controllers;

use App\Data\User\UserUpdateData;
use App\Events\User\UserUpdatedEmail;
use App\Events\User\UserUpdatedPassword;
use App\Exceptions\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

class UserController extends Controller
{
    public function me(): JsonResponse
    {
        Gate::authorize('view', $this->user);

        return UserResource::make($this->user)->response();
    }

    public function update(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $user = $userService->update(
            UserUpdateData::from([
                'name' => $request->input('name'),
                'user' => $this->user,
            ])
        );

        return UserResource::make($user)->response();
    }

    /**
     * @throws InternalServerErrorException
     */
    public function updateEmail(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', "not_in:{$this->user->email}", 'unique:users'],
        ]);

        try {
            $oldEmail = $this->user->email;
            $user = $userService->update(
                UserUpdateData::from([
                    'email' => strtolower($request->input('email')),
                    'user' => $this->user,
                ])
            );

            UserUpdatedEmail::dispatch($user, $oldEmail);

            return UserResource::make($user)->response();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function updatePassword(Request $request, UserService $userService): JsonResponse
    {
        Gate::authorize('update', $this->user);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:32', 'different:old_password', 'confirmed'],
        ]);

        try {
            $user = $userService->update(
                UserUpdateData::from([
                    'password' => $request->input('password'),
                    'user' => $this->user,
                ])
            );

            UserUpdatedPassword::dispatch($user);

            return UserResource::make($user)->response();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }
}
