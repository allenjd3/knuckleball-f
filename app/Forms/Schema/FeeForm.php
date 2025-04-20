<?php

namespace App\Forms\Schema;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class FeeForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('amount')->required(),
            Select::make('fee_material_id')
                ->label('Material')
                ->relationship(name: 'feeMaterial', titleAttribute: 'name')
                ->preload()
                ->required()
                ->searchable()
                ->createOptionModalHeading('Create Item')
                ->createOptionForm([
                    TextInput::make('name')->required(),
                ]),
        ];
    }
}
