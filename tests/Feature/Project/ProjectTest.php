<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\{ getJson, postJson, deleteJson, actingAs, assertDatabaseHas, assertDatabaseMissing };
use function Pest\Faker\fake;

describe('Track', function () {
    beforeEach(function () {
        $this->endpoint = '/api/projects/';
        $this->jsonStructure = ['id', 'title'];
        $this->user = User::factory()->create();

        actingAs($this->user);
    });

    describe('GET /api/projects', function () {
        it('[Guest] Cannot view', function () {
            Auth::logout();
            getJson($this->endpoint)->assertUnauthorized();
        });

        it('[Authenticated user] View only own', function () {
            $projectsForUser = Project::factory()
                ->for($this->user)
                ->count(3)
                ->create();

            Project::factory()
                ->for(User::factory()->create())
                ->count(2)
                ->create();

            getJson($this->endpoint)
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure(['data' => ['*' => $this->jsonStructure]])
                ->assertJsonFragment(['id' => $projectsForUser->first()->id]);
        });

        it('[Authenticated user] Empty collection if no have', function () {
            getJson($this->endpoint)
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    describe('POST /api/projects', function () {
        beforeEach(function () {
            $this->title = fake()->text(100);
        });

        it('[Guests] Cannot create', function () {
            Auth::logout();
            postJson($this->endpoint, ['title' => $this->title])->assertUnauthorized();
        });

        it('[Authenticated user] Create with all fields', function () {
            postJson($this->endpoint, [
                'title' => $this->title,
            ])
                ->assertCreated()
                ->assertJsonStructure(['data' => $this->jsonStructure])
                ->assertJsonFragment(['title' => $this->title]);

            assertDatabaseHas(Project::class, [
                'user_id' => $this->user->id,
                'title' => $this->title,
            ]);
        });

        describe('Validation', function () {
            describe('Title', function () {
                it('is required', function () {
                    postJson($this->endpoint, ['title' => null])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field is required.'));
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
        });
    });

    describe('POST /api/projects/{project}', function () {
        beforeEach(function () {
            $this->project = Project::factory()->for($this->user)->create(['title' => fake()->text(100)]);
        });

        it('[Guests] Cannot update', function () {
            Auth::logout();
            postJson($this->endpoint . $this->project->id, ['title' => 'Attempt to update'])
                ->assertUnauthorized();
        });

        it('[Authenticated user] Cannot update another user\'s model', function () {
            $modelOfAnotherUser = Project::factory()
                ->for(User::factory()->create())
                ->create();

            postJson($this->endpoint . $modelOfAnotherUser->id, ['title' => 'Malicious Update'])
                ->assertForbidden();
            assertDatabaseHas(Project::class, ['id' => $modelOfAnotherUser->id, 'title' => $modelOfAnotherUser->title]);
        });

        it('[Authenticated user] Update title', function () {
            postJson($this->endpoint . $this->project->id, ['title' => 'Updated Title'])
                ->assertOk()
                ->assertJsonFragment(['title' => 'Updated Title']);

            assertDatabaseHas(Project::class, [
                'id' => $this->project->id,
                'title' => 'Updated Title',
            ]);
        });

        it('returns 404 if trying to update non-existent', function () {
            postJson($this->endpoint . '99999', ['title' => fake()->text(100)])
                ->assertNotFound();
        });

        describe('Validation', function () {
            describe('Title', function () {
                it('is sometimes required', function () {
                    postJson($this->endpoint . $this->project->id, ['title' => null])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field is required.'));
                });

                it('must be a string', function () {
                    postJson($this->endpoint . $this->project->id, ['title' => 1])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must be a string.'));
                });

                it('must not be greater than 1000 characters', function () {
                    postJson($this->endpoint . $this->project->id, ['title' => str_repeat('a', 1001)])
                        ->assertJsonValidationErrors('title')
                        ->assertJson(fn(AssertableJson $json) => assertValidationErrorMessage($json, 'title', 'The title field must not be greater than 1000 characters.'));
                });
            });
        });
    });

    describe('DELETE /api/projects/{project}', function () {
        beforeEach(function () {
            $this->model = Project::factory()->for($this->user)->create();
        });

        it('[Guests] Cannot delete', function () {
            Auth::logout();
            deleteJson($this->endpoint . $this->model->id)->assertUnauthorized();
        });

        it('[Authenticated user] Can delete', function () {
            deleteJson($this->endpoint . $this->model->id)
                ->assertOk();

            assertDatabaseMissing(Project::class, ['id' => $this->model->id]);
        });

        it('[Authenticated user] Cannot delete another user\'s model', function () {
            $modelOfAnotherUser = Project::factory()->for(User::factory()->create())->create();

            deleteJson($this->endpoint . $modelOfAnotherUser->id)
                ->assertForbidden();
            assertDatabaseHas(Project::class, ['id' => $modelOfAnotherUser->id]);
        });

        it('returns 404 if trying to delete non-existent model', function () {
            deleteJson($this->endpoint . '99999')
                ->assertNotFound();
        });
    });
});
