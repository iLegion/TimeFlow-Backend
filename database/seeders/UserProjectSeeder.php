<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserProjectSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(5)->has(Project::factory(5))->create();
    }
}