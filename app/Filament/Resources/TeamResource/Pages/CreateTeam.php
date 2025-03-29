<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use App\Models\Team;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $team = Team::create(collect($data)->except('url')->toArray());

        if ($url = data_get($data, 'url')) {
            $team->media()->create(['url' => $url]);
        }

        return $team;
    }
}
