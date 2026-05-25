<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\PostalMail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'postal_mail_id' => PostalMail::factory(),
            'manufacturer' => $this->faker->words(2, asText: true),
            'series' => $this->faker->words(2, asText: true),
            'year' => rand(1950, 2025),
        ];
    }
}
