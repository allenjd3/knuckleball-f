<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Player;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        Player::get()
            ->each(
                fn ($player) => Address::factory()->state(['player_id' => $player->id])->create()
            );
    }
}
