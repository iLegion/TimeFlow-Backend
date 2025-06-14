<?php

use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\{ getJson, postJson, deleteJson, actingAs, assertDatabaseHas, assertDatabaseMissing };
use function Pest\Faker\fake;

describe('Track', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->endpoint = '/api/tracks';
        $this->jsonStructure = ['id', 'title', 'project', 'started_at', 'finished_at'];

        actingAs($this->user);
    });

    describe('GET /api/tracks', function () {
        it('[Guest] Cannot view tracks', function () {
            Auth::logout();
            getJson($this->endpoint)->assertUnauthorized();
        });

        // Maybe add test for getting track by week and more.
        it('[Authenticated user] View their tracks', function () {
            $tracksForUser = Track::factory()
                ->for($this->user)
                ->count(3)
                ->create(['started_at' => now()->subDay(), 'finished_at' => now()]);

            Track::factory()
                ->for(User::factory()->create())
                ->count(2)
                ->create();

            getJson($this->endpoint)
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure(['data' => ['*' => $this->jsonStructure]])
                ->assertJsonFragment(['id' => $tracksForUser->first()->id]);
        });

        it('[Authenticated user] Empty collection if they have no tracks', function () {
            getJson($this->endpoint)
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });

        it('[Authenticated user] View tracks for the last week by default', function () {
            Track::factory()
                ->for($this->user)
                ->create(['started_at' => now()->subDays(2), 'finished_at' => now()->subDays(2)->addHour()]);
            Track::factory()
                ->for($this->user)
                ->create(['started_at' => now()->subDays(5), 'finished_at' => now()->subDays(5)->addHour()]);

            Track::factory()
                ->for($this->user)
                ->create(['started_at' => now()->subDays(8), 'finished_at' => now()->subDays(8)->addHour()]);

            Track::factory()
                ->for($this->user)
                ->create(['started_at' => now()->subDay(), 'finished_at' => null]);

            Track::factory()
                ->for(User::factory()->create())
                ->create(['started_at' => now()->subDay(), 'finished_at' => now()]);

            getJson($this->endpoint)
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure(['data' => ['*' => $this->jsonStructure]]);
        });

        it('[Authenticated user] View tracks within a specified date range (from and to)', function () {
            $targetFrom = today()->subDays(10);
            $targetTo = today()->subDays(5);

            $track1 = Track::factory()->for($this->user)->create([
                'started_at' => today()->subDays(8),
                'finished_at' => today()->subDays(8)->addHour()
            ]);

            $track2 = Track::factory()->for($this->user)->create([
                'started_at' => $targetFrom->copy()->startOfDay(),
                'finished_at' => $targetFrom->copy()->startOfDay()->addHour()
            ]);

            $track3 = Track::factory()->for($this->user)->create([
                'started_at' => $targetTo->copy()->endOfDay(),
                'finished_at' => $targetTo->copy()->endOfDay()->addHour()
            ]);

            Track::factory()->for($this->user)->create([
                'started_at' => today()->subDays(15),
                'finished_at' => today()->subDays(15)->addHour()
            ]);

            Track::factory()->for($this->user)->create([
                'started_at' => today()->subDays(3),
                'finished_at' => today()->subDays(3)->addHour()
            ]);

            Track::factory()->for($this->user)->create([
                'started_at' => today()->subDays(7),
                'finished_at' => null
            ]);

            Track::factory()->for(User::factory()->create())->create([
                'started_at' => today()->subDays(7),
                'finished_at' => today()->subDays(7)->addHour()
            ]);

            $queryParams = http_build_query([
                'from' => $targetFrom->format('Y-m-d'),
                'to' => $targetTo->format('Y-m-d'),
            ]);

            getJson("$this->endpoint?$queryParams", [
                'query' => [
                    'from' => $targetFrom->format('Y-m-d'),
                    'to' => $targetTo->format('Y-m-d'),
                ]
            ])
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonFragment(['id' => $track1->id])
                ->assertJsonFragment(['id' => $track2->id])
                ->assertJsonFragment(['id' => $track3->id]);
        });

        describe('Validation', function () {
            describe('From', function () {
                it('Fails validation if only "from" is provided', function () {
                    $queryParams = http_build_query([
                        'from' => '2023-01-01',
                    ]);

                    getJson("$this->endpoint?$queryParams")
                        ->assertUnprocessable()
                        ->assertJsonValidationErrors(['to']);
                });

                it('Fails validation if only "to" is provided', function () {
                    $queryParams = http_build_query([
                        'to' => '2023-01-31',
                    ]);

                    getJson("$this->endpoint?$queryParams")
                        ->assertUnprocessable()
                        ->assertJsonValidationErrors(['from']);
                });

                it('Fails validation if "from" date is invalid', function () {
                    $queryParams = http_build_query([
                        'from' => 'invalid-date',
                        'to' => '2023-01-31'
                    ]);

                    getJson("$this->endpoint?$queryParams")
                        ->assertUnprocessable()
                        ->assertJsonValidationErrors(['from']);
                });

                it('Fails validation if "to" date is invalid', function () {
                    $queryParams = http_build_query([
                        'from' => '2023-01-01',
                        'to' => 'invalid-date',
                    ]);

                    getJson("$this->endpoint?$queryParams")
                        ->assertUnprocessable()
                        ->assertJsonValidationErrors(['to']);
                });
            });
        });
    });

    describe('GET /api/tracks/active', function () {
        it('[Guests] Cannot view active track', function () {
            Auth::logout();
            getJson($this->endpoint . '/active')->assertUnauthorized();
        });

        it('[Authenticated user] View their active track', function () {
            $activeTrack = Track::factory()->for($this->user)->create(['finished_at' => null]);

            Track::factory()->for($this->user)->create(['finished_at' => now()]);

            getJson($this->endpoint . '/active')
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $activeTrack->id,
                        'title' => $activeTrack->title,
                        'finished_at' => null,
                    ]
                ]);
        });

        it('[Authenticated user] Gets null if no active track', function () {
            Track::factory()->for($this->user)->count(2)->create(['finished_at' => now()]);

            getJson($this->endpoint . '/active')
                ->assertOk()
                ->assertJson(['data' => null]);
        });

        it('[Authenticated user] Not get active track of another user', function () {
            Track::factory()
                ->for(User::factory()->create())
                ->create(['finished_at' => null]);

            getJson($this->endpoint . '/active')
                ->assertOk()
                ->assertJson(['data' => null]);
        });
    });

    describe('POST /api/tracks', function () {
        beforeEach(function () {
            $this->title = fake()->text(100);
            $this->project = Project::factory()->for($this->user)->create();
        });

        it('[Guests] Cannot create a track', function () {
            Auth::logout();
            postJson($this->endpoint, ['title' => $this->title])->assertUnauthorized();
        });

        it('[Authenticated user] Create a track with only a title', function () {
            postJson($this->endpoint, ['title' => $this->title])
                ->assertCreated()
                ->assertJsonFragment(['title' => $this->title])
                ->assertJsonStructure(['data' => ['id', 'title', 'started_at', 'finished_at']]);

            assertDatabaseHas('tracks', [
                'user_id' => $this->user->id,
                'title' => $this->title,
                'finished_at' => null,
            ]);
        });

        it('[Authenticated user] Create a track with only a project', function () {
            postJson($this->endpoint, ['project_id' => $this->project->id])
                ->assertCreated()
                ->assertJsonFragment(['project' => ProjectResource::make($this->project)])
                ->assertJsonStructure(['data' => $this->jsonStructure]);

            assertDatabaseHas('tracks', [
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'finished_at' => null,
            ]);
        });

        it('[Authenticated user] Create a track with all fields', function () {
            $startedAt = now()->subHour();
            $finishedAt = now();

            postJson($this->endpoint, [
                'title' => $this->title,
                'project_id' => $this->project->id,
                'started_at' => $startedAt->format('Y-m-d H:i:s'),
                'finished_at' => $finishedAt->format('Y-m-d H:i:s'),
            ])
                ->assertCreated()
                ->assertJsonFragment(['title' => $this->title])
                ->assertJsonFragment(['project' => ProjectResource::make($this->project)])
                ->assertJsonFragment(['started_at' => $startedAt->format('Y-m-d H:i:s')])
                ->assertJsonFragment(['finished_at' => $finishedAt->format('Y-m-d H:i:s')]);

            assertDatabaseHas('tracks', [
                'user_id' => $this->user->id,
                'project_id' => $this->project->id,
                'title' => $this->title,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
            ]);
        });

        describe('Validation', function () {
            describe('Project', function () {
                it('is sometimes required', function () {
                    postJson($this->endpoint, ['project_id' => null])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The project field is required.'));
                });

                it('is int', function () {
                    postJson($this->endpoint, ['project_id' => 'test'])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The project field must be an integer.'));
                });

                it('is exists', function () {
                    postJson($this->endpoint, ['project_id' => 100])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The selected project is invalid.'));
                });
            });

            describe('Title', function () {
                it('is nullable', function () {
                    postJson($this->endpoint, ['title' => null])
                        ->assertJsonMissingValidationErrors('title');
                });

                it('must be a string', function () {
                    postJson($this->endpoint, ['title' => 1])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must be a string.'));
                });

                it('must not be greater than 1000 characters', function () {
                    postJson($this->endpoint, ['title' => str_repeat('a', 1001)])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must not be greater than 1000 characters.'));
                });
            });

            describe('Started at', function () {
                it('is nullable', function () {
                    postJson($this->endpoint, ['started_at' => null])
                        ->assertJsonMissingValidationErrors('started_at');
                });

                it('must match the format Y-m-d H:i:s', function () {
                    postJson($this->endpoint, ['started_at' => '1999-01-01'])
                        ->assertJsonValidationErrors('started_at')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'started_at', 'The started at field must match the format Y-m-d H:i:s.'));
                });
            });

            describe('Finished at', function () {
                it('is nullable', function () {
                    postJson($this->endpoint, ['finished_at' => null])
                        ->assertJsonMissingValidationErrors('finished_at');
                });

                it('must match the format Y-m-d H:i:s', function () {
                    postJson($this->endpoint, ['finished_at' => '1999-01-01'])
                        ->assertJsonValidationErrors('finished_at')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'finished_at', 'The finished at field must match the format Y-m-d H:i:s.'));
                });
            });
        });
    });

    describe('POST /api/tracks/{track}', function () {
        beforeEach(function () {
            $this->track = Track::factory()->for($this->user)->create(['title' => fake()->text(100)]);
        });

        it('[Guests] cannot update a track', function () {
            Auth::logout();
            postJson($this->endpoint . '/' . $this->track->id, ['title' => 'Attempt to update'])
                ->assertUnauthorized();
        });

        it('[Authenticated user] Cannot update another user\'s track', function () {
            $trackOfAnotherUser = Track::factory()
                ->for(User::factory()->create())
                ->create();

            postJson($this->endpoint . '/' . $trackOfAnotherUser->id, ['title' => 'Malicious Update'])
                ->assertForbidden();
            assertDatabaseHas('tracks', ['id' => $trackOfAnotherUser->id, 'title' => $trackOfAnotherUser->title]);
        });

        it('[Authenticated user] Update their own track title', function () {
            postJson($this->endpoint . '/' . $this->track->id, ['title' => 'Updated Title'])
                ->assertOk()
                ->assertJsonFragment(['title' => 'Updated Title']);

            assertDatabaseHas('tracks', [
                'id' => $this->track->id,
                'title' => 'Updated Title',
            ]);
        });

        it('[Authenticated user] Update their own track dates', function () {
            $newStartedAt = now()->subMinutes(30);
            $newFinishedAt = now();

            postJson($this->endpoint . '/' . $this->track->id, [
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

        test('[Authenticated user] Update their own track project', function () {
            $project = Project::factory()->for($this->user)->create();

            postJson($this->endpoint . '/' . $this->track->id, [
                'project_id' => $project->id,
            ])
                ->assertOk()
                ->assertJsonFragment(['project' => ProjectResource::make($project)]);

            assertDatabaseHas('tracks', [
                'id' => $this->track->id,
                'project_id' => $project->id,
            ]);
        });

        it('returns 404 if trying to update non-existent track', function () {
            postJson($this->endpoint . '/99999', ['title' => fake()->text(100)])
                ->assertNotFound();
        });

        describe('Validation', function () {
            describe('Project', function () {
                it('is sometimes required', function () {
                    postJson($this->endpoint . '/' . $this->track->id, ['project_id' => null])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The project field is required.'));
                });

                it('is int', function () {
                    postJson($this->endpoint . '/' . $this->track->id, ['project_id' => 'test'])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The project field must be an integer.'));
                });

                it('is exists', function () {
                    postJson($this->endpoint . '/' . $this->track->id, ['project_id' => 100])
                        ->assertJsonValidationErrors('project_id')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'project_id', 'The selected project is invalid.'));
                });
            });

            describe('Title', function () {
                it('is nullable', function () {
                    postJson($this->endpoint . '/' . $this->track->id, ['title' => null])
                        ->assertJsonMissingValidationErrors('title');
                });

                it('must be a string', function () {
                    postJson($this->endpoint . '/' . $this->track->id, ['title' => 1])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must be a string.'));
                });

                it('must not be greater than 1000 characters', function () {
                    postJson('/api/tracks/' . $this->track->id, ['title' => str_repeat('a', 1001)])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must not be greater than 1000 characters.'));
                });
            });

            describe('Started at', function () {
                it('is nullable', function () {
                    postJson('/api/tracks/' . $this->track->id, ['started_at' => null])
                        ->assertJsonMissingValidationErrors('started_at');
                });

                it('must match the format Y-m-d H:i:s', function () {
                    postJson('/api/tracks/' . $this->track->id, ['started_at' => '1999-01-01'])
                        ->assertJsonValidationErrors('started_at')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'started_at', 'The started at field must match the format Y-m-d H:i:s.'));
                });
            });

            describe('Finished at', function () {
                it('is nullable', function () {
                    postJson('/api/tracks/' . $this->track->id, ['finished_at' => null])
                        ->assertJsonMissingValidationErrors('finished_at');
                });

                it('must match the format Y-m-d H:i:s', function () {
                    postJson('/api/tracks/' . $this->track->id, ['finished_at' => '1999-01-01'])
                        ->assertJsonValidationErrors('finished_at')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'finished_at', 'The finished at field must match the format Y-m-d H:i:s.'));
                });
            });
        });
    });

    describe('DELETE /api/tracks/{track}', function () {
        beforeEach(function () {
            $this->track = Track::factory()->for($this->user)->create();
        });

        it('[Guests] Cannot delete a track', function () {
            Auth::logout();
            deleteJson('/api/tracks/' . $this->track->id)->assertUnauthorized();
        });

        it('[Authenticated user] Can delete their own track', function () {
            deleteJson('/api/tracks/' . $this->track->id)
                ->assertOk();

            assertDatabaseMissing('tracks', ['id' => $this->track->id]);
        });

        it('[Authenticated user] Cannot delete another user\'s track', function () {
            $anotherUser = User::factory()->create();
            $trackOfAnotherUser = Track::factory()->for($anotherUser)->create();

            deleteJson('/api/tracks/' . $trackOfAnotherUser->id)
                ->assertForbidden();
            assertDatabaseHas('tracks', ['id' => $trackOfAnotherUser->id]);
        });

        it('returns 404 if trying to delete non-existent track', function () {
            deleteJson('/api/tracks/99999')
            ->assertNotFound();
        });
    });
});
