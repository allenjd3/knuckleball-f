<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Fee;
use App\Models\Feed;
use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mention>
 */
class MentionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'user_id' => User::factory(),
            'mentioned_by_id' => User::factory(),
            'feed_type' => Feed::factory()->make()->type,
        ];
    }

    public function forUser(User $user, string $type, ?string $comment)
    {
        $feed = match($type) {
            $type === 'comment' => Feed::factory()->forUser($user)->comment($comment ?? "Mentioned @{$user->handle}")->create(),
            $type === 'postal_mail' => Feed::factory()->forUser($user)->postalMail()->create(),
        };

        return $this->state([
            'feed_id' => $feed->id,
            'user_id' => User::factory(),
            'mentioned_by' => $user->id,
            'feed_type' => $feed->type,
        ]);
    }
}
