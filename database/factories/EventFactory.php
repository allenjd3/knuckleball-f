<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type'       => 'player_signing',
            'name'       => fake()->words(3, true),
            'start_date' => fake()->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'user_id'    => User::factory(),
            'status'     => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved', 'approved_at' => now()]);
    }

    public function playerSigning(?Player $player = null): static
    {
        return $this->state([
            'type'          => 'player_signing',
            'event_subtype' => 'in_person',
            'player_id'     => $player?->id ?? Player::factory(),
        ]);
    }

    public function mailIn(?Player $player = null): static
    {
        return $this->state([
            'type'          => 'player_signing',
            'event_subtype' => 'mail_in',
            'player_id'     => $player?->id ?? Player::factory(),
        ]);
    }

    public function cardShow(): static
    {
        return $this->state([
            'type' => 'card_show',
        ]);
    }

    public function withLocation(float $lat = 40.7128, float $lng = -74.0060): static
    {
        return $this->state([
            'latitude'  => $lat,
            'longitude' => $lng,
            'city'      => 'New York',
            'state'     => 'NY',
        ]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
