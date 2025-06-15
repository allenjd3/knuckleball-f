<?php

namespace App\Jobs;

use App\Actions\ParseDates;
use App\Models\ImportData;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Support\Dtos\AddressDto;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
            ->lazyById(100)
            ->each(function (ImportData $importData) {
                $team = Team::firstWhere('name', data_get($importData->data, 'team'));
                $user = User::firstWhere('name', data_get($importData->data, 'user'));
                $lastTeam = Team::firstWhere('name', data_get($importData->data, 'lastTeam'));

                $player = Player::firstOrCreate(
                    [
                        'name' => data_get($importData->data, 'name'),
                        'team_id' => $team?->id ?? null,
                    ],
                    [
                        'retired_at' => ParseDates::handle(data_get($importData->data, 'retired_at')),
                        'user_id' => $user?->id ?? null,
                        'published_at' => ParseDates::handle(data_get($importData->data, 'published_at')),
                        'last_team_id' => $lastTeam?->id ?? null,
                    ],
                );

                $address = data_get($importData->data, 'address');
                $addressDto = AddressDto::make(explode("\n", $address));

                $player->addresses()
                    ->create([
                        'address_1' => $addressDto->address1,
                        'address_2' => $addressDto->address2,
                        'signer_id' => $player->id,
                        'city' => $addressDto->city,
                        'state' => $addressDto->state,
                        'postal_code' => $addressDto->zip,
                        'published_at' => now()->subDay(),
                    ]);

                $this->importDataIds[] = $importData->id;
            });

        ImportData::whereIn('id', $this->importDataIds)->delete();
    }
}
