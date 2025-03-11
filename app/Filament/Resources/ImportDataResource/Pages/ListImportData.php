<?php

namespace App\Filament\Resources\ImportDataResource\Pages;

use App\Filament\Resources\ImportDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportData extends ListRecords
{
    protected static string $resource = ImportDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
