<?php

namespace App\Jobs;

use App\Models\ImportData;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Fluent;

class ProcessPlayerData implements ShouldBeUnique, ShouldQueue
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
                $player = Player::firstOrCreate([
                    'name' => data_get($importData->data, 'name'),
                    'team_id' => Team::firstWhere('name', data_get($importData->data, 'team'))?->id,
                ]);

                $address = data_get($importData->data, 'address');

                $addressLines = explode("\n", $address);
                if (count($addressLines) === 4) {
                    [$name, $address1, $address2, $cityStateZip] = $addressLines;
                } elseif (count($addressLines) === 3) {
                    [$address1, $address2, $cityStateZip] = $addressLines;
                } elseif (count($addressLines) === 2) {
                    [$address1, $cityStateZip] = $addressLines;
                } else {
                    $importData->update([
                        'errors' => 'We didn\'t count the correct number of lines. We counted ' . count($addressLines),
                    ]);

                    return;
                }

                $sanitizedData = $this->sanitizeCityStateZip($cityStateZip);

                $player->address()
                    ->create([
                        'address_1' => $address1,
                        'address_2' => $address2 ?? '',
                        'city' => $sanitizedData->city,
                        'state' => $sanitizedData->state,
                        'postal_code' => $sanitizedData->zip,
                    ]);

                $this->importDataIds[] = $importData->id;
            });

        ImportData::whereIn('id', $this->importDataIds)->delete();
    }

    private function sanitizeCityStateZip(string $cityStateZip): Fluent
    {
        $matches = explode(' ', $cityStateZip);

        $zip = trim(array_pop($matches), ' ,');
        $state = trim(array_pop($matches), ' ,');
        $city = trim(implode(' ', $matches), ' ,');

        return fluent([
            'zip' => $zip,
            'state' => $state,
            'city' => $city,
        ]);
    }
}
