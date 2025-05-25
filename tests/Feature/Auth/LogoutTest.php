<?php

use App\Models\User;

use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use function Pest\Laravel\{ getJson, postJson, withHeaders, assertDatabaseHas, assertDatabaseMissing, assertAuthenticated, assertGuest, actingAs };
use function Pest\Faker\fake;

describe('Logout', function () {
    beforeEach(function () {
        $this->email = strtolower(fake()->unique()->safeEmail());
        $this->password = fake()->password(8, 32);
        $this->user = User::factory()->create(['email' => $this->email, 'password' => bcrypt($this->password)]);
    });

    test('logout a user with an api token', function () {
        /** @var NewAccessToken $token */
        $token = $this->user->generateNewToken();

        assertDatabaseHas(PersonalAccessToken::class, [
            'id' => $token->accessToken->id,
        ]);

        withHeaders(['Authorization' => 'Bearer ' . $token->plainTextToken])
            ->postJson('/api/auth/logout')
            ->assertOk();

        assertDatabaseMissing(PersonalAccessToken::class, [
            'id' => $token->accessToken->id,
        ]);
    });

//    test('logout a user that is authenticated with session via actingAs', function () {
//        getJson('/sanctum/csrf-cookie')->assertStatus(204);
//
//        postJson('/api/auth/login', ['email' => $this->email, 'password' => $this->password])
//            ->assertStatus(200);
//
//        assertAuthenticated('web');
//
//        postJson('/api/auth/logout')->assertOk();
//
//        assertGuest('web');
//    });

    test('logout for unauthenticated', function () {
        postJson('/api/auth/logout')
            ->assertStatus(401);
    });

    it('logout the current token if user has multiple tokens', function () {
        /** @var NewAccessToken $tokenOne */
        $tokenOne = $this->user->generateNewToken();
        /** @var NewAccessToken $tokenTwo */
        $tokenTwo = $this->user->generateNewToken();

        assertDatabaseHas(PersonalAccessToken::class, [
            'id' => $tokenOne->accessToken->id,
        ]);

        assertDatabaseHas(PersonalAccessToken::class, [
            'id' => $tokenTwo->accessToken->id,
        ]);

        withHeaders(['Authorization' => 'Bearer ' . $tokenOne->plainTextToken])
            ->postJson('/api/auth/logout')
            ->assertOk();

        assertDatabaseMissing(PersonalAccessToken::class, [
            'id' => $tokenOne->accessToken->id,
        ]);

        assertDatabaseHas(PersonalAccessToken::class, [
            'id' => $tokenTwo->accessToken->id,
        ]);
    });

    it('logout with an invalid token', function () {
        $invalidToken = 'invalid-token-string';

        postJson('/api/auth/logout', [], ['Authorization' => 'Bearer ' . $invalidToken])
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    });
});
