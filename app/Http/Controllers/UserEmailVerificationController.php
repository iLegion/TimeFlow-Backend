<?php

namespace App\Http\Controllers;

use App\Data\User\UserUpdateData;
use App\Events\User\UserEmailVerificationRequested;
use App\Exceptions\InternalServerErrorException;
use App\Services\User\UserEmailVerificationService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

class UserEmailVerificationController extends Controller
{
    /**
     * @throws InternalServerErrorException
     */
    public function send(UserEmailVerificationService $service): JsonResponse
    {
        Gate::authorize('process-email_verification', $this->user);

        try {
            $service->create($this->user->email);

            UserEmailVerificationRequested::dispatch($this->user);

            return response()->json();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function verify(Request $request, UserEmailVerificationService $service, UserService $userService): JsonResponse
    {
        Gate::authorize('process-email_verification', $this->user);

        $request->validate([
            'code' => ['required', 'string', 'size:5'],
        ]);

        try {
            $codeFromCache = $service->get($this->user->email);
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }

        $request->validate([
            'code' => ["in:$codeFromCache"],
        ]);

        try {
            $userService->update(UserUpdateData::from([
                'email_verified_at' => now(),
                'user' => $this->user,
            ]));
            $service->delete($this->user->email);

            return response()->json();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }
}