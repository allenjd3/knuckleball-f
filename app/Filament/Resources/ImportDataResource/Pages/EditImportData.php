<?php

namespace App\Filament\Resources\ImportDataResource\Pages;

use App\Filament\Resources\ImportDataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImportData extends EditRecord
{
    protected static string $resource = ImportDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
