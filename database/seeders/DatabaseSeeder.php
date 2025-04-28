<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\Production\FeedSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call([
                FeedSeeder::class,
            ]);
        } else {
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

            PostalMail::factory(50)
                ->has(
                    Card::factory(3)->for($user)
                )->create();

            $materials = FeeMaterial::limit(5)
                ->pluck('id');

            PostalMail::get()
                ->each(
                    fn ($postalMail) => $postalMail
                        ->feeMaterials()
                        ->sync($materials->shuffle()->toArray())
                );

            $this->call([
                TagSeeder::class,
                FeedSeeder::class,
            ]);
        }
    }
}
