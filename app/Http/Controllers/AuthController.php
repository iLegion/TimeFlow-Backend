<?php

namespace App\Http\Controllers;

use App\DTO\User\UserCreateDTO;
use App\Events\User\UserRegistered;
use App\Exceptions\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    /**
     * @throws InternalServerErrorException
     */
    public function register(Request $request, UserService $userService): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:32', 'confirmed'],
        ]);

        try {
            $user = $userService->create(
                new UserCreateDTO(
                    $request->input('name'),
                    $request->input('email'),
                    $request->input('password')
                )
            );
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }

        UserRegistered::dispatch($user);

        try {
            Auth::login($user);

            return UserResource::make($user)
                ->additional(['token' => $user->generateNewToken()->plainTextToken])
                ->response();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:32'],
        ]);

        $credentials = [
            'email' => strtolower($request->input('email')),
            'password' => $request->get('password'),
        ];

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'password' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        return UserResource::make($user)
            ->additional(['token' => $user->generateNewToken()->plainTextToken])
            ->response();
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json();
    }
}
