<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => fake()->company(),
            'city'    => fake()->city(),
            'state'   => fake()->stateAbbr(),
            'user_id' => User::factory(),
            'status'  => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved', 'approved_at' => now()]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function withLocation(float $lat = 40.7128, float $lng = -74.0060): static
    {
        return $this->state([
            'latitude'  => $lat,
            'longitude' => $lng,
        ]);
    }
}
