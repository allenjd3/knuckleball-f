<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\FeeMaterial;
use App\Models\Signer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'amount' => $this->faker->randomNumber(),
            'signer_id' => Signer::factory(),
            'fee_material_id' => FeeMaterial::factory(),
            'published_at' => Carbon::now(),
        ];
    }
}
