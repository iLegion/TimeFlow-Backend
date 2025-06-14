<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(10)->create();

        $usersCreatedMonthAgo = User::factory()
            ->state(['created_at' => now()->subMonth()])
            ->count(1)
            ->create();

        $usersCreatedMonthAgo->each(function (User $user) {
            $projects = Project::factory()->for($user)->count(3)->create();

            $projects->each(function (Project $project) use ($user) {
                Track::factory()
                    ->for($user)
                    ->for($project)
                    ->count(5)
                    ->create();
            });
        });

        $usersCreatedMonthAndTwoWeeksAndDeletedAgo = User::factory()
            ->state(['created_at' => now()->subMonth()->subWeeks(2), 'deleted_at' => now()->subWeeks(2)])
            ->count(1)
            ->create();

        $usersCreatedMonthAndTwoWeeksAndDeletedAgo->each(function (User $user) {
            $projects = Project::factory()->for($user)->count(3)->create();

            $projects->each(function (Project $project) use ($user) {
                Track::factory()
                    ->for($user)
                    ->for($project)
                    ->count(5)
                    ->create();
            });
        });
    }
}
