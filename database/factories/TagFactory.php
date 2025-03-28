<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->words(5, true),
            'category' => array_keys(Tag::categories())[rand(0, 3)],
            'published_at' => rand(0, 1) ? now()->subWeek() : null,
        ];
    }
}
