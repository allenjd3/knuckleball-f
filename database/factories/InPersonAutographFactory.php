<?php

namespace Database\Factories;

use App\Models\FeeMaterial;
use App\Models\InPersonAutograph;
use App\Models\Signer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InPersonAutographFactory extends Factory
{
    protected $model = InPersonAutograph::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'signer_id' => Signer::factory(),
            'obtained_date' => $this->faker->dateTimeBetween('-1 year')->format('Y-m-d'),
            'fee_material_id' => FeeMaterial::factory(),
            'location' => $this->faker->city(),
            'comment' => $this->faker->boolean() ? $this->faker->sentence() : null,
        ];
    }
}
