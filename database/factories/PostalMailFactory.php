<?php

namespace Database\Factories;

use App\Models\FeeMaterial;
use App\Models\PostalMail;
use App\Models\Signer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PostalMailFactory extends Factory
{
    protected $model = PostalMail::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'user_id' => User::factory(),
            'fee_material_id' => FeeMaterial::factory(),
            'signer_id' => Signer::factory(),
            'date_sent' => Carbon::now(),
            'returned_date' => rand(0, 1) ? Carbon::now() : null,
            'is_failed' => false,
            'comment' => $this->faker->words($this->faker->numberBetween(3, 6), true),
        ];
    }

    public function returned()
    {
        return $this->state([
            'date_sent' => now()->subWeeks(2),
            'returned_date' => now()->subWeek(),
        ]);
    }

    public function unReturned()
    {
        return $this->state([
            'date_sent' => now()->subWeeks(2),
            'returned_date' => null,
        ]);
    }

    public function failed()
    {
        return $this->state([
            'date_sent' => now()->subWeeks(2),
            'is_failed' => true,
            'returned_date' => null,
        ]);
    }
}
