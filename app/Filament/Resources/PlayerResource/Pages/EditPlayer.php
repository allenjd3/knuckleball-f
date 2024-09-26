<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use App\Models\Player;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate (Model $record, array $data): Model
    {
        $data = collect($data);

        $record->update($data->only(['name', 'team_id', 'published_at'])->toArray());

        if ($url = data_get($data, 'url')) {
            $record->media()->create(['url' => $url]);
        }

        return $record;
    }
}
