<?php

namespace App\Filament\Imports;

use App\Jobs\ProcessPlayerData;
use App\Models\ImportData;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ImportDataImporter extends Importer
{
    protected static ?string $model = ImportData::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['name'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('address')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['address'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('team')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['team'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('user')
                ->rules(['nullable'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['user'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['published_at'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('lastTeam')
                ->rules(['nullable'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['lastTeam'] = $state;
                    $record->data = $data;
                }),
            ImportColumn::make('retired_at')
                ->rules(['nullable', 'date'])
                ->fillRecordUsing(function ($record, string $state): void {
                    $data = $record->data;
                    $data['retired_at'] = $state;
                    $record->data = $data;
                }),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your import data import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): ?ImportData
    {
        return new ImportData;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return null;
    }

    protected function afterSave(): void
    {
        ProcessPlayerData::dispatch()->delay(now()->addMinutes(1));
    }
}
