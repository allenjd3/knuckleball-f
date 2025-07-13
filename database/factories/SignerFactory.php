<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

class SignerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'signable_id' => Player::factory(),
            'signable_type' => 'player',
        ];
    }
}
