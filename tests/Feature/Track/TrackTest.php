<?php

use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\{ getJson, postJson, deleteJson, actingAs, assertDatabaseHas, assertDatabaseMissing };
use function Pest\Faker\fake;

describe('Track', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        actingAs($this->user);
    });

    describe('GET /api/tracks', function () {
        test('guests cannot view tracks', function () {
            Auth::logout();
            getJson('/api/tracks')->assertUnauthorized();
        });

        // Maybe add test for getting track by week and more.
        test('authenticated user can view their tracks', function () {
            $tracksForUser = Track::factory()
                ->for($this->user)
                ->count(3)
                ->create(['started_at' => now()->subDay(), 'finished_at' => now()]);

            Track::factory()
                ->for(User::factory()->create())
                ->count(2)
                ->create();

            getJson('/api/tracks')
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'started_at', 'finished_at']
                    ]
                ])
                ->assertJsonFragment(['id' => $tracksForUser->first()->id]);
        });

        test('authenticated user sees an empty collection if they have no tracks', function () {
            getJson('/api/tracks')
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    describe('GET /api/tracks/active', function () {
        test('guests cannot view active track', function () {
            Auth::logout();
            getJson('/api/tracks/active')->assertUnauthorized();
        });

        test('authenticated user can view their active track', function () {
            $activeTrack = Track::factory()->for($this->user)->create(['finished_at' => null]);

            Track::factory()->for($this->user)->create(['finished_at' => now()]);

            getJson('/api/tracks/active')
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $activeTrack->id,
                        'title' => $activeTrack->title,
                        'finished_at' => null,
                    ]
                ]);
        });

        test('authenticated user gets null data if no active track', function () {
            Track::factory()->for($this->user)->count(2)->create(['finished_at' => now()]);

            getJson('/api/tracks/active')
                ->assertOk()
                ->assertJson(['data' => null]);
        });

        test('authenticated user does not get active track of another user', function () {
            Track::factory()
                ->for(User::factory()->create())
                ->create(['finished_at' => null]);

            getJson('/api/tracks/active')
                ->assertOk()
                ->assertJson(['data' => null]);
        });
    });

    describe('POST /api/tracks', function () {
        beforeEach(function () {
            $this->title = fake()->text(100);
        });

        test('guests cannot create a track', function () {
            Auth::logout();
            postJson('/api/tracks', ['title' => $this->title])->assertUnauthorized();
        });

        test('authenticated user can create a track with only a title', function () {
            postJson('/api/tracks', ['title' => $this->title])
                ->assertCreated()
                ->assertJsonFragment(['title' => $this->title])
                ->assertJsonStructure(['data' => ['id', 'title', 'started_at', 'finished_at']]);

            assertDatabaseHas('tracks', [
                'user_id' => $this->user->id,
                'title' => $this->title,
                'finished_at' => null,
            ]);
        });

        test('authenticated user can create a track with all fields', function () {
            $startedAt = now()->subHour();
            $finishedAt = now();

            postJson('/api/tracks', [
                'title' => $this->title,
                'started_at' => $startedAt->format('Y-m-d H:i:s'),
                'finished_at' => $finishedAt->format('Y-m-d H:i:s'),
            ])
                ->assertCreated()
                ->assertJsonFragment(['title' => $this->title])
                ->assertJsonFragment(['started_at' => $startedAt->format('Y-m-d H:i:s')])
                ->assertJsonFragment(['finished_at' => $finishedAt->format('Y-m-d H:i:s')]);

            assertDatabaseHas('tracks', [
                'user_id' => $this->user->id,
                'title' => $this->title,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
            ]);
        });

        test('creating a track validates title max length', function () {
            postJson('/api/tracks', ['title' => str_repeat('a', 1001)])
                ->assertJsonValidationErrors('title');
        });

        test('creating a track validates date format', function () {
            postJson('/api/tracks', ['started_at' => 'invalid-date'])
                ->assertJsonValidationErrors('started_at');

            postJson('/api/tracks', ['finished_at' => 'not-a-date'])
                ->assertJsonValidationErrors('finished_at');
        });
    });

    describe('POST /api/tracks/{track}', function () {
        beforeEach(function () {
            $this->track = Track::factory()->for($this->user)->create(['title' => fake()->text(100)]);
        });

        test('guests cannot update a track', function () {
            Auth::logout();
            postJson('/api/tracks/' . $this->track->id, ['title' => 'Attempt to update'])
                ->assertUnauthorized();
        });

        test('authenticated user cannot update another user\'s track', function () {
            $trackOfAnotherUser = Track::factory()
                ->for(User::factory()->create())
                ->create();

            postJson('/api/tracks/' . $trackOfAnotherUser->id, ['title' => 'Malicious Update'])
                ->assertForbidden();
            assertDatabaseHas('tracks', ['id' => $trackOfAnotherUser->id, 'title' => $trackOfAnotherUser->title]);
        });

        test('authenticated user can update their own track title', function () {
            postJson('/api/tracks/' . $this->track->id, ['title' => 'Updated Title'])
                ->assertOk()
                ->assertJsonFragment(['title' => 'Updated Title']);

            assertDatabaseHas('tracks', [
                'id' => $this->track->id,
                'title' => 'Updated Title',
            ]);
        });

        test('authenticated user can update their own track dates', function () {
            $newStartedAt = now()->subMinutes(30);
            $newFinishedAt = now();

            postJson('/api/tracks/' . $this->track->id, [
                'started_at' => $newStartedAt->format('Y-m-d H:i:s'),
                'finished_at' => $newFinishedAt->format('Y-m-d H:i:s'),
            ])
                ->assertOk()
                ->assertJsonFragment(['started_at' => $newStartedAt->format('Y-m-d H:i:s')])
                ->assertJsonFragment(['finished_at' => $newFinishedAt->format('Y-m-d H:i:s')]);

            assertDatabaseHas('tracks', [
                'id' => $this->track->id,
                'started_at' => $newStartedAt,
                'finished_at' => $newFinishedAt,
            ]);
        });

        test('updating a track validates title max length', function () {
            postJson('/api/tracks/' . $this->track->id, ['title' => str_repeat('b', 1001)])
                ->assertJsonValidationErrors('title');
        });

        test('updating a track validates date format', function () {
            postJson('/api/tracks/' . $this->track->id, ['started_at' => 'invalid-date-update'])
                ->assertJsonValidationErrors('started_at');
        });

        test('returns 404 if trying to update non-existent track', function () {
            postJson('/api/tracks/99999', ['title' => fake()->text(100)])
            ->assertNotFound();
        });
    });

    describe('DELETE /api/tracks/{track}', function () {
        beforeEach(function () {
            $this->track = Track::factory()->for($this->user)->create();
        });

        test('guests cannot delete a track', function () {
            Auth::logout();
            deleteJson('/api/tracks/' . $this->track->id)->assertUnauthorized();
        });

        test('authenticated user can delete their own track', function () {
            deleteJson('/api/tracks/' . $this->track->id)
                ->assertOk();

            assertDatabaseMissing('tracks', ['id' => $this->track->id]);
        });

        test('authenticated user cannot delete another user\'s track', function () {
            $anotherUser = User::factory()->create();
            $trackOfAnotherUser = Track::factory()->for($anotherUser)->create();

            deleteJson('/api/tracks/' . $trackOfAnotherUser->id)
                ->assertForbidden();
            assertDatabaseHas('tracks', ['id' => $trackOfAnotherUser->id]);
        });

        test('returns 404 if trying to delete non-existent track', function () {
            deleteJson('/api/tracks/99999')
            ->assertNotFound();
        });
    });
});
