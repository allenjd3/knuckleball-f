<?php

namespace Database\Factories;

use App\Models\FeeMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeeMaterialFactory extends Factory
{
    protected $model = FeeMaterial::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->word(),
            'published_at' => now()->subWeek(),
        ];
    }
}
