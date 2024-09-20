<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition (): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->name(),
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'slug' => $this->faker->slug(),
            'published_at' => Carbon::now(),
        ];
    }
}
