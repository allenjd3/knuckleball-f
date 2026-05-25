<?php

namespace Database\Factories;

use App\Models\InviteCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InviteCode>
 */
class InviteCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->word,
            'remaining' => rand(3, 5),
            'is_unlimited' => false,
        ];
    }
}
