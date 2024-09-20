<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->teamNames()[$this->faker->numberBetween(0, 49)],
            'user_id' => User::factory(),
            'published_at' => Carbon::now(),
        ];
    }

    private function teamNames(): array
    {
        return [
            'Thunderbolts',
            'Warriors',
            'Dragons',
            'Cyclones',
            'Titans',
            'Sharks',
            'Lions',
            'Eagles',
            'Rangers',
            'Pirates',
            'Gladiators',
            'Panthers',
            'Vikings',
            'Spartans',
            'Hawks',
            'Falcons',
            'Wildcats',
            'Knights',
            'Cobras',
            'Grizzlies',
            'Storm',
            'Raiders',
            'Phoenix',
            'Bears',
            'Tigers',
            'Mavericks',
            'Rhinos',
            'Jets',
            'Suns',
            'Bulls',
            'Wolves',
            'Mustangs',
            'Cougars',
            'Comets',
            'Scorpions',
            'Rockets',
            'Predators',
            'Outlaws',
            'Rebels',
            'Blazers',
            'Pioneers',
            'Crusaders',
            'Miners',
            'Hurricanes',
            'Patriots',
            'Buccaneers',
            'Bulldogs',
            'Firebirds',
            'Gators',
            'Hornets',
        ];
    }
}
