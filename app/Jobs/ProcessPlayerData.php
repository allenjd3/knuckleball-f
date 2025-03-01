<?php

namespace App\Jobs;

use App\Models\ImportData;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;

class ProcessPlayerData implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public array $importDataIds = [];

    public function uniqueId(): string
    {
        return 'player-data';
    }

    public function handle(): void
    {
        ImportData::query()
            ->lazy(100)
            ->each(function (ImportData $importData) {
                $player = Player::create([
                    'name' => data_get($importData->data, 'name'),
                    'team_id' => Team::firstWhere('name', data_get($importData->data, 'team'))->id,
                ]);

                $address = data_get($importData->data, 'address');
                $addressLines = explode("\n", $address);
                if (count($addressLines) === 3) {
                    [$address1, $address2, $cityStateZip] = $addressLines;
                } elseif (count($addressLines) === 2) {
                    [$address1, $cityStateZip] = $addressLines;
                } else {
                    Log::error('we didnt count the right number of lines');
                    return;
                }

                $matches = explode(" ", $cityStateZip);

                $zip = trim(array_pop($matches), " ,");
                $state = trim(array_pop($matches), " ,");
                $city = trim(implode(" ", $matches), " ,");

                $player->address()
                    ->create([
                    'address_1' => $address1,
                    'address_2' => $address2 ?? '',
                    'city' => trim($city, " ,"),
                    'state' => $state,
                    'postal_code' => $zip,
                ]);

                $this->importDataIds[] = $importData->id;
            });

        ImportData::whereIn('id', $this->importDataIds)->delete();
    }
}
