<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'James Allen',
            'email' => 'james@example.com',
            'super_admin' => true,

        ]);

        User::factory()->create([
            'name' => 'Brittany Allen',
            'email' => 'britt@example.com',
            'super_admin' => false,
        ]);

        $teams = Team::factory(10)
            ->for($user)
            ->create();

        $teams->each(fn ($team) => Player::factory(12)->for($team)->create());

        $this->call([
            TagSeeder::class,
        ]);
    }
}
