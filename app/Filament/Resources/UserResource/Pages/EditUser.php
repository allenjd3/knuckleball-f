<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('email'),
                DatePicker::make('published_at'),
                Checkbox::make('super_admin')->columnSpan(2),
                Select::make('role')
                    ->options(Role::class),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        collect($data)->each(fn (mixed $value, string $key) => $record->{$key} = $value);
        $record->save();

        return $record;
    }
}
