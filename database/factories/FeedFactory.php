<?php

namespace Database\Factories;

use App\Models\Player;
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

    public function postalMail(?Player $player = null, ?User $user = null)
    {
        $user ??= User::factory()->create();
        $player ??= Player::factory()->create();

        return $this->state([
            'followable_id' => $user->id,
            'meta' => [
                'player' => $player->name,
                'player_path' => $player->path(),
                'user' => $user->name,
                'user_path' => $user->path(),
                'photo' => $user->profile_photo_url,
                'date_sent' => now()->subWeek(),
                'date_returned' => rand(0, 1) ? now()->subDay() : null,
            ],
        ]);
    }

    public function forUser(User $user, $model = PostalMail::class)
    {
        return $this->state([
            'feedable_id' => $model::factory()->for($user)->create()->id,
            'feedable_type' => $model,
            'followable_id' => $user->id,
        ]);
    }
}
