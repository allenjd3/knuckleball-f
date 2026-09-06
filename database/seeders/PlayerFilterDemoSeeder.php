<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Fee;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates one clearly-named, published player per players-table filter/sort
 * scenario, so each filter in ViewPlayers has something to actually match.
 */
class PlayerFilterDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create();
        $category = Category::first() ?? Category::factory()->create();

        $team = Team::factory()->published()->for($user)->create([
            'name' => 'Filter Demo Team',
            'category_id' => $category->id,
        ]);

        // Status filter: active / retired (flagged) / retired (past date,
        // no flag) / retired (scheduled, future date — should read Active) / deceased.
        $this->player($team, 'Zzz Active Zzz');

        $this->player($team, 'Zzz Retired Flagged Zzz', ['is_retired' => true]);

        $this->player($team, 'Zzz Retired Past Date Zzz', ['retired_at' => now()->subYear()]);

        $this->player($team, 'Zzz Retired Scheduled Zzz', ['retired_at' => now()->addYear()]);

        $this->player($team, 'Zzz Deceased Zzz', ['deceased_at' => now()->subYear()]);

        // Team + Category filters, and the "last name" sort: same last name,
        // different first names, so ordering by last_name groups them.
        $this->player($team, 'Alice Zambrano');
        $this->player($team, 'Bob Zambrano');

        // Requires a fee.
        $withFee = $this->player($team, 'Zzz Requires A Fee Zzz');
        Fee::factory()->create(['signer_id' => $withFee->signer->id]);

        // Has a photo.
        $withPhoto = $this->player($team, 'Zzz Has A Photo Zzz');
        $withPhoto->media()->create(['url' => 'avatars/demo-photo.jpg']);

        // Tags (approved, so they actually show up under the Tags filter).
        $tagged = $this->player($team, 'Zzz Tagged Verified Zzz');
        if ($tag = Tag::first()) {
            $tagged->tags()->attach($tag->id, ['approved_at' => now(), 'user_id' => $user->id]);
        }

        // Response rate buckets: high (mostly returned), medium, low
        // (mostly unreturned/failed), plus pending mail that shouldn't
        // count against the rate at all.
        $this->responseRatePlayer($team, 'Zzz High Responder Zzz', returned: 4, unreturned: 1);
        $this->responseRatePlayer($team, 'Zzz Medium Responder Zzz', returned: 2, unreturned: 3);
        $this->responseRatePlayer($team, 'Zzz Low Responder Zzz', returned: 1, unreturned: 4);

        // Return in last 90 days.
        $recentlyActive = $this->player($team, 'Zzz Recently Active Zzz');
        PostalMail::factory()->create([
            'signer_id' => $recentlyActive->signer->id,
            'returned_date' => now()->subDays(10),
            'is_failed' => false,
        ]);
    }

    private function player(Team $team, string $name, array $attributes = []): Player
    {
        return Player::factory()->published()->for($team)->create([
            'name' => $name,
            ...$attributes,
        ]);
    }

    private function responseRatePlayer(Team $team, string $name, int $returned, int $unreturned): Player
    {
        $player = $this->player($team, $name);

        PostalMail::factory()->count($returned)->create([
            'signer_id' => $player->signer->id,
            'returned_date' => now()->subWeek(),
            'is_failed' => false,
        ]);

        PostalMail::factory()->count($unreturned)->create([
            'signer_id' => $player->signer->id,
            'returned_date' => null,
            'is_failed' => true,
        ]);

        return $player;
    }
}
