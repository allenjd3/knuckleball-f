<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeaturedListingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->approved(),
            'user_id' => User::factory(),
            'plan_type' => 'one_time',
            'amount_paid' => 9.99,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ];
    }

    public function subscription(): static
    {
        return $this->state([
            'plan_type' => 'monthly',
            'expires_at' => null,
            'stripe_subscription_id' => 'sub_test_' . fake()->lexify('??????????'),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'expires_at' => now()->subDay(),
        ]);
    }
}
