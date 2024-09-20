<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition (): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'url' => $this->faker->url(),
            'imageable_id' => $this->faker->randomNumber(),
            'imageable_type' => $this->faker->word(),
        ];
    }
}
