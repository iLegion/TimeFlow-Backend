<?php

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\{ postJson, assertDatabaseHas };
use function Pest\Faker\fake;

describe('Register', function () {
    beforeEach(function () {
        $this->name = fake()->name();
        $this->email = strtolower(fake()->unique()->safeEmail());
        $this->password = fake()->password(8, 32);
    });

    it('registers a new user', function () {
        postJson('/api/auth/register', [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'token'
            ])
            ->assertJson([
                'data' => ['name' => $this->name, 'email' => $this->email],
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json
                    ->whereType('token', 'string')
                    ->where('token', fn ($token) => !empty($token))
                    ->etc()
            );

        /** @var User $user */
        $user = Auth::user();

        assertDatabaseHas(User::class, ['name' => $this->name, 'email' => $this->email]);
        expect(Auth::check())
            ->toBeTrue()
            ->and($user->email)
            ->toBe($this->email)
            ->and(Hash::check($this->password, $user->password))
            ->toBeTrue();
    });

    it('registers a new user even if email has different casing', function () {
        $mixedCaseEmail = 'TeStUsEr@eXaMpLe.CoM';
        $lowerCaseEmail = strtolower($mixedCaseEmail);

        postJson('/api/auth/register', [
            'name' => $this->name,
            'email' => $mixedCaseEmail,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'token'
            ])
            ->assertJson([
                'data' => ['name' => $this->name, 'email' => $lowerCaseEmail],
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json
                ->whereType('token', 'string')
                ->where('token', fn ($token) => !empty($token))
                ->etc()
            );

        assertDatabaseHas(User::class, ['name' => $this->name, 'email' => $lowerCaseEmail]);
    });

    describe('Validation', function () {
        describe('Name', function () {
            it('is required', function () {
                postJson('/api/auth/register', [
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('name')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'name', 'The name field is required.'));
            });

            it('must be a string', function () {
               postJson('/api/auth/register', [
                    'name' => fake()->randomElements(),
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('name')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'name', 'The name field must be a string.'));
            });

            it('must not be greater than 255 characters', function () {
                postJson('/api/auth/register', [
                    'name' => str_repeat('a', 256),
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('name')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'name', 'The name field must not be greater than 255 characters.'));
            });
        });

        describe('Email', function () {
            it('is required', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field is required.'));
            });

            it('must be a string', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => fake()->randomElements(),
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must be a string.'));
            });

            it('must be a valid email address', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => fake()->text(),
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must be a valid email address.'));
            });

            it('must not be greater than 255 characters', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => str_repeat('a', 244) . '@testing.com',
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email field must not be greater than 255 characters.'));
            });

            it('already been taken', function () {
                User::factory()->create(['email' => $this->email]);

                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('email')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'email', 'The email has already been taken.'));
            });
        });

        describe('Password', function () {
            it('is required', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password_confirmation' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field is required.'));
            });

            it('must be a string', function () {
                $password = fake()->randomElements();

                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $password,
                    'password_confirmation' => $password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must be a string.'));
            });

            it('must be at least 8 characters', function () {
                $password = fake()->password(7, 7);

                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $password,
                    'password_confirmation' => $password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must be at least 8 characters.'));
            });

            it('must not be greater than 32 characters', function () {
                $password = fake()->password(33, 33);

                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $password,
                    'password_confirmation' => $password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field must not be greater than 32 characters.'));
            });

            it('confirmation does not match', function () {
                postJson('/api/auth/register', [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password,
                ])
                    ->assertStatus(422)
                    ->assertJsonValidationErrors('password')
                    ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'password', 'The password field confirmation does not match.'));
            });
        });
    });

    describe('Error Handling', function () {
        it('returns a 500 error if user creation fails', function () {
            $this->mock(UserService::class, function ($mock) {
                $mock->shouldReceive('create')
                    ->once()
                    ->andThrow(new Exception('Simulated user creation failure.'));
            });

            postJson('/api/auth/register', [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password,
            ])
                ->assertStatus(500)
                ->assertJson([
                    'message' => 'Internal Server Error.'
                ]);
        });
    });
});
