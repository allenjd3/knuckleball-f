<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->name(),
            'team_id' => Team::factory(),
            'user_id' => null,
            'slug' => $this->faker->slug(),
            'published_at' => $this->faker->numberBetween(0, 1) ? Carbon::now() : null,
        ];
    }
}
