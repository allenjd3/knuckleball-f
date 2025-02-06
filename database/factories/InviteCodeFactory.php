<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InviteCode>
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
