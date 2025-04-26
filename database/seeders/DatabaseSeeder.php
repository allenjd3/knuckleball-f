<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Feed;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Team;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
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
        Feed::factory(5)->forUser($user)->postalMail()->create();

    PostalMail::factory(50)
            ->has(
                Card::factory(3)->for($user)
            )->create()
            ->each(
                fn ($mail) => $mail->feeds()->create(
                    Feed::factory()->postalMail()->make()->only('comment', 'meta')
                )
            );

        $materials = FeeMaterial::limit(5)
            ->pluck('id');
        PostalMail::get()->each(fn ($postalMail) => $postalMail->feeMaterials()->sync($materials->shuffle()->toArray()));

        $this->call([
            TagSeeder::class,
        ]);
    }
}
