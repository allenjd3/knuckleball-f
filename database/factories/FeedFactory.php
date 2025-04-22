<?php

namespace Database\Factories;

use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feed>
 */
class FeedFactory extends Factory
{
    public function definition(): array
    {
        $model = PostalMail::class;
        return [
            'feedable_id' => $model::factory()->create(),
            'feedable_type' => $model,
            'comment' => $this->faker->paragraph(),
        ];
    }

    public function forUser(User $user, $model = PostalMail::class)
    {
        return $this->state([
            'feedable_id' => $model::factory()->for($user)->create()->id,
            'feedable_type' => $model,
        ]);
    }
}
