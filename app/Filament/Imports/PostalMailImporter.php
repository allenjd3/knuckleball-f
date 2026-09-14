<?php

namespace App\Filament\Imports;

use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Throwable;

class PostalMailImporter extends Importer
{
    protected static ?string $model = PostalMail::class;

    protected int $feeMaterialId;

    public static function getColumns(): array
    {
        return [
            // player_name, item, and the card columns below don't map to real
            // `postal_mails` columns — they're read via getData() in
            // resolveRecord()/saveRecord() instead, so they get a no-op
            // fillRecordUsing to stop the default fill from writing them
            // straight onto the PostalMail record (which would fail to save).
            ImportColumn::make('player_name')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('date_sent')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('returned_date')
                ->ignoreBlankState()
                ->rules(['nullable', 'date']),
            ImportColumn::make('item')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('manufacturer')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('series')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('year')
                ->integer()
                ->rules(['nullable', 'integer'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('number')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('variation')
                ->rules(['nullable'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('comment')
                ->ignoreBlankState()
                ->rules(['nullable']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your return import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import (duplicates or errors).';
        }

        return $body;
    }

    public function resolveRecord(): ?PostalMail
    {
        $data = $this->getData();

        if (blank($data['player_name'] ?? null)) {
            throw new RowImportFailedException('player_name is required.');
        }

        if (blank($data['date_sent'] ?? null)) {
            throw new RowImportFailedException('date_sent is required.');
        }

        try {
            $dateSent = Carbon::parse($data['date_sent']);
        } catch (Throwable) {
            throw new RowImportFailedException("Could not parse date_sent \"{$data['date_sent']}\".");
        }

        $player = $this->findOrCreatePlayer($data['player_name']);
        $signer = $player->signer;

        $existingMail = PostalMail::query()
            ->where('signer_id', $signer->id)
            ->where('user_id', auth()->id())
            ->whereDate('date_sent', $dateSent)
            ->first();

        if ($existingMail && $this->cardAlreadyLogged($existingMail, $data)) {
            throw new RowImportFailedException("Duplicate: you already have a return from {$player->name} on {$dateSent->toDateString()} with this card logged.");
        }

        $feeMaterial = FeeMaterial::firstOrCreate(['name' => filled($data['item'] ?? null) ? $data['item'] : 'Card']);
        $this->feeMaterialId = $feeMaterial->id;

        if ($existingMail) {
            $existingMail->fee_material_id = $this->feeMaterialId;

            return $existingMail;
        }

        return new PostalMail([
            'user_id' => auth()->id(),
            'signer_id' => $signer->id,
            'date_sent' => $dateSent,
            'fee_material_id' => $this->feeMaterialId,
            'is_failed' => false,
        ]);
    }

    public function saveRecord(): void
    {
        parent::saveRecord();

        $this->record->feeMaterials()->syncWithoutDetaching([$this->feeMaterialId]);

        $data = $this->getData();

        if (filled($data['manufacturer'] ?? null)) {
            $this->record->cards()->create([
                'user_id' => auth()->id(),
                'manufacturer' => $data['manufacturer'],
                'series' => $data['series'] ?? '',
                'year' => $data['year'] ?? null,
                'number' => $data['number'] ?? null,
                'variation' => $data['variation'] ?? null,
            ]);
        }
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return null;
    }

    protected function findOrCreatePlayer(string $name): Player
    {
        $slug = Str::slug($name);

        if ($player = Player::where('slug', $slug)->first()) {
            return $player;
        }

        $bestMatch = null;
        $bestScore = 0.0;

        Player::where('slug', 'like', substr($slug, 0, 3) . '%')
            ->get(['id', 'name', 'slug'])
            ->each(function (Player $candidate) use ($slug, &$bestMatch, &$bestScore): void {
                similar_text($slug, $candidate->slug, $percent);

                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $bestMatch = $candidate;
                }
            });

        if ($bestMatch && $bestScore >= 90.0) {
            return $bestMatch;
        }

        return Player::create([
            'name' => $name,
            'published_at' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function cardAlreadyLogged(PostalMail $mail, array $data): bool
    {
        if (blank($data['manufacturer'] ?? null)) {
            return false;
        }

        return $mail->cards()
            ->whereRaw('lower(manufacturer) = ?', [strtolower($data['manufacturer'])])
            ->whereRaw('lower(series) = ?', [strtolower($data['series'] ?? '')])
            ->where('year', $data['year'] ?? null)
            ->where('number', $data['number'] ?? null)
            ->exists();
    }
}
