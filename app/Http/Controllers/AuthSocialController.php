<?php

namespace App\Http\Controllers;

use App\Data\SocialProvider\SocialProviderCreateData;
use App\Data\User\UserCreateData;
use App\Exceptions\InternalServerErrorException;
use App\Models\User;
use App\Services\SocialProvider\SocialProviderService;
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
    public function callbackGoogle(UserService $userService, SocialProviderService $socialProviderService): RedirectResponse
    {
        try {
            $oUser = Socialite::driver('google')->stateless()->user();
            $user = User::query()->where('email', $oUser->email)->first();

            if (!$user) {
                $user = $userService->create(
                    UserCreateData::from([
                        'email' => $user->email,
                        'name' => $user->name ?? 'User-From-Google_' . $user->id,
                        'password' => bcrypt(Str::random(8)),
                        'email_verified_at' => now(),
                    ])
                );

            }

            Auth::login($user);

            if (!$user->socialProviders()->where('provider_name', 'google')->exists()) {
                $socialProviderService->create(
                    SocialProviderCreateData::from([
                        'user' => $user,
                        'provider_id' => $oUser->id,
                        'provider_name' => 'google',
                    ])
                );
            }
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }

        return response()->redirectTo(config('app.front_url'));
    }

    public function redirectGithub(): SymfonyRedirectResponse|RedirectResponse
    {
        return Socialite::driver('github')->stateless()->redirect();
    }

    /**
     * @throws InternalServerErrorException
     */
    public function callbackGithub(UserService $userService, SocialProviderService $socialProviderService): RedirectResponse
    {
        try {
            $oUser = Socialite::driver('github')->stateless()->user();
            $user = User::query()->where('email', $oUser->email)->first();

            if (!$user) {
                $user = $userService->create(
                    UserCreateData::from([
                        'email' => $user->email,
                        'name' => $user->name ?? 'User-From-Github_' . $user->id,
                        'password' => bcrypt(Str::random(8)),
                        'email_verified_at' => now(),
                    ])
                );
            }

            Auth::login($user);

            if (!$user->socialProviders()->where('provider_name', 'github')->exists()) {
                $socialProviderService->create(
                    SocialProviderCreateData::from([
                        'user' => $user,
                        'provider_id' => $oUser->id,
                        'provider_name' => 'github',
                    ])
                );
            }

            return response()->redirectTo(config('app.front_url'));
        } catch (Throwable $e) {
            throw new InternalServerErrorException($e);
        }
    }
}
