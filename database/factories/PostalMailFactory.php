<?php

namespace Database\Factories;

use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PostalMailFactory extends Factory
{
    protected $model = PostalMail::class;

    public function definition (): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'fee_material_id' => FeeMaterial::factory(),
            'player_id' => Player::factory(),
            'date_sent' => Carbon::now(),
            'returned_date' => Carbon::now(),
            'comment' => $this->faker->words($this->faker->numberBetween(3, 6), true),
        ];
    }
}
