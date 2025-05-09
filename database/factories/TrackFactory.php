<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomStartedAt = fake()->dateTimeBetween(now()->subWeeks(2), now()->toDate());
        $randomFinishedAt = fake()->dateTimeBetween($randomStartedAt, Carbon::parse($randomStartedAt)->addMonths()->toDate());

        return [
            'title' => fake()->text,
            'started_at' => $randomStartedAt,
            'finished_at' => $randomFinishedAt,
        ];
    }
}
