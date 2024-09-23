<?php

namespace Database\Factories;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition (): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'amount' => $this->faker->randomNumber(),
            'player_id' => $this->faker->randomNumber(),
            'published_at' => Carbon::now(),
        ];
    }
}
