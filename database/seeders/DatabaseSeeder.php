<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'James Allen',
            'email' => 'james@example.com',
        ]);

        $teams = Team::factory(10)
            ->for($user)
            ->create();

        $teams->each(fn ($team) => Player::factory(12)->for($team)->create());
    }
}
