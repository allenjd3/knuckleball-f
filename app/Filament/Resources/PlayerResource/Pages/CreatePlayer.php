<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use App\Models\Player;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlayer extends CreateRecord
{
    protected static string $resource = PlayerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data = collect($data);
        $player = Player::create($data->only(['name', 'team_id', 'published_at'])->toArray());

        if ($url = data_get($data, 'url')) {
            $player->media()->create(['url' => $url]);
        }

        return $player;
    }
}
