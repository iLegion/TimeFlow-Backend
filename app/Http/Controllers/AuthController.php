<?php

namespace App\Http\Controllers;

use App\DTO\User\UserCreateDTO;
use App\Exceptions\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
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

            Auth::login($user);

            return $this->response(
                UserResource::make($user),
                ['token' => $user->generateNewToken()->plainTextToken]
            );
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }

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
            return $this->responseError('The provided credentials are incorrect.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = Auth::user();

        return $this->response(
            UserResource::make($user),
            ['token' => $user->generateNewToken()->plainTextToken]
        );
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

        return $this->response();
    }
}
