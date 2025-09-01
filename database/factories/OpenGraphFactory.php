<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OpenGraphFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url' => $this->faker->url,
            'path' => $this->faker->word() . '.png',
            'disk' => 'local',
            'title' => $this->faker->words(3, true),
            'description' => str($this->faker->words(50, true))->limit(230),
        ];
    }
}
