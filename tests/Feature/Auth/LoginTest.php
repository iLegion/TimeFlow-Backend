<?php

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\{ postJson };
use function Pest\Faker\fake;

describe('Login', function () {
    beforeEach(function () {
        $this->email = strtolower(fake()->unique()->safeEmail());
        $this->password = fake()->password(8, 32);

        User::factory()->create([
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);
    });

    it('login a user', function () {
        postJson('/api/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'token'
            ])
            ->assertJson([
                'data' => ['email' => $this->email],
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json
                    ->whereType('token', 'string')
                    ->where('token', fn ($token) => !empty($token))
                    ->etc()
            );

        /** @var User $user */
        $user = Auth::user();

        expect(Auth::check())
            ->toBeTrue()
            ->and($user->email)
            ->toBe($this->email);
    });

    it('logs in a user even if email has different casing', function () {
        $mixedCaseEmail = 'TeStUsEr@eXaMpLe.CoM';
        $lowerCaseEmail = strtolower($mixedCaseEmail);

        User::factory()->create([
            'email' => $lowerCaseEmail,
            'password' => bcrypt($this->password),
        ]);

        postJson('/api/auth/login', [
            'email' => $mixedCaseEmail,
            'password' => $this->password,
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => ['email' => $lowerCaseEmail],
            ]);

        /** @var User $user */
        $user = Auth::user();

        expect(Auth::check())
            ->toBeTrue()
            ->and($user->email)
            ->toBe($lowerCaseEmail);
    });

    it('logs in a user with a password of minimum length', function () {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8, 8);

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'token'
            ])
            ->assertJson([
                'data' => ['email' => $email],
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json
                ->whereType('token', 'string')
                ->where('token', fn ($token) => !empty($token))
                ->etc()
            );

        /** @var User $user */
        $user = Auth::user();

        expect(Auth::check())
            ->toBeTrue()
            ->and($user->email)
            ->toBe($email);
    });

    it('logs in a user with a password of maximum length', function () {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(32, 32);

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'token'
            ])
            ->assertJson([
                'data' => ['email' => $email],
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json
                ->whereType('token', 'string')
                ->where('token', fn ($token) => !empty($token))
                ->etc()
            );

        /** @var User $user */
        $user = Auth::user();

        expect(Auth::check())
            ->toBeTrue()
            ->and($user->email)
            ->toBe($email);
    });

    it('not attempt email', function () {
        postJson('/api/auth/login', [
            'email' => fake()->unique()->safeEmail(),
            'password' => $this->password,
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);

        expect(Auth::check())->toBeFalse();
    });

    it('not attempt password', function () {
        postJson('/api/auth/login', [
            'email' => $this->email,
            'password' => fake()->unique()->password(8, 32),
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);

        expect(Auth::check())->toBeFalse();
    });

    it('rate limits attempts', function () {
        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(1)->by($request->user()?->id ?: $request->ip());
        });

        postJson('/api/auth/login', [
            'email' => $this->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(422);

        postJson('/api/auth/login', [
            'email' => $this->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(429)
            ->assertJson(['message' => 'Too Many Attempts.'])
            ->assertHeader('Retry-After');
    });

    describe('Validation', function () {
        describe('Email', function () {
            it('is required', function () {
                postJson('/api/auth/login', [
                    'password' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field is required.'));
            });

            it('must be a string', function () {
                postJson('/api/auth/login', [
                    'email' => fake()->randomElements(),
                    'password' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must be a string.'));
            });

            it('must be a valid email address', function () {
                postJson('/api/auth/login', [
                    'email' => fake()->text(),
                    'password' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must be a valid email address.'));
            });

            it('must not be greater than 255 characters', function () {
                postJson('/api/auth/login', [
                    'email' => str_repeat('a', 244) . '@testing.com',
                    'password' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must not be greater than 255 characters.'));
            });
        });

        describe('Password', function () {
            it('is required', function () {
                postJson('/api/auth/login', [
                    'email' => $this->email,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field is required.'));
            });

            it('must be a string', function () {
                postJson('/api/auth/login', [
                    'email' => $this->email,
                    'password' => fake()->randomElements(),
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must be a string.'));
            });

            it('must be at least 8 characters', function () {
                postJson('/api/auth/login', [
                    'email' => $this->email,
                    'password' => fake()->password(7, 7),
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must be at least 8 characters.'));
            });

            it('must not be greater than 32 characters', function () {
                postJson('/api/auth/login', [
                    'email' => $this->email,
                    'password' => fake()->password(33, 33),
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must not be greater than 32 characters.'));
            });
        });
    });
});
