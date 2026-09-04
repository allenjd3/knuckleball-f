<?php

namespace App\Filament\Imports;

use App\Actions\ParseDates;
use App\Models\Player;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PlayerImporter extends Importer
{
    protected static ?string $model = Player::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('team')
                ->requiredMapping()
                ->relationship(resolveUsing: ['name'])
                ->rules(['required']),
            ImportColumn::make('user')
                ->relationship(resolveUsing: ['email', 'name'])
                ->rules(['nullable']),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('lastTeam')
                ->relationship(resolveUsing: ['name'])
                ->rules(['nullable']),
            ImportColumn::make('retired_at')
                ->rules(['nullable'])
                ->fillRecordUsing(function (Player $record, ?string $state): void {
                    // Some source lists give a retirement year, others just say
                    // "Retired" with no year. Either way we don't want that to
                    // silently fall back to "Active" — parse a year if we can,
                    // but always flag is_retired when anything was provided.
                    $record->retired_at = ParseDates::handle($state);

                    if (filled($state)) {
                        $record->is_retired = true;
                    }
                }),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your player import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): ?Player
    {
        return new Player;
    }
}
