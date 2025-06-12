<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 12345678;
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        $this->command->info("User created: $user->email / $password");

        $projects = Project::factory()->for($user)->count(5)->create();

        $this->command->info("Projects created: " . $projects->pluck('id'));

        $projects->each(function (Project $project) use ($user) {
            $tracks = Track::factory()
                ->for($user)
                ->for($project)
                ->count(50)
                ->create();

            $this->command->info("Tracks created: Project ID: $project->id. Tracks IDs: " . $tracks->pluck('id'));
        });
    }
}