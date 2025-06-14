<?php

namespace App\Http\Controllers;

use App\Data\User\UserCreateData;
use App\Exceptions\InternalServerErrorException;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class AuthSocialController extends Controller
{
    public function redirectGoogle(): SymfonyRedirectResponse|RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * @throws InternalServerErrorException
     */
    public function callbackGoogle(UserService $userService): RedirectResponse
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }

        $existingUser = User::query()->where('email', $user->email)->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            $newUser = $userService->create(
                UserCreateData::from([
                    'email' => $user->email,
                    'name' => $user->name ?? 'User-From-Google_' . $user->id,
                    'password' => bcrypt(Str::random(8)),
                    'email_verified_at' => now(),
                ])
            );

            Auth::login($newUser);
        }

        return response()->redirectTo(config('app.front_url'));
    }
}
