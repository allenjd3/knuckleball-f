<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use App\Livewire\ViewPlayers;
use App\Models\Player;
use App\Services\DuplicatePlayerFinder;
use App\Services\PlayerMergeService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class DuplicatePlayers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PlayerResource::class;

    protected string $view = 'filament.resources.player-resource.pages.duplicate-players';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Player::query()
                ->whereIn('id', app(DuplicatePlayerFinder::class)->find()->flatten(1)->pluck('id'))
                ->selectRaw("players.*, CONCAT(LOWER(TRIM(name)), '-', COALESCE(team_id, 0)) as duplicate_key")
                ->with(['team', 'media', 'signer.postalMails', 'signer.addresses', 'signer.fees']))
            ->groups([
                Group::make('duplicate_key')
                    ->label('Duplicate group')
                    ->getTitleFromRecordUsing(fn (Player $record) => "{$record->name} — " . ($record->team?->name ?? 'No team')),
            ])
            ->defaultGroup('duplicate_key')
            ->defaultSort('created_at')
            ->columns([
                ImageColumn::make('media.url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(ViewPlayers::placeholderAvatarUrl()),
                TextColumn::make('name')
                    ->description(fn (Player $record) => "#{$record->id}")
                    ->url(fn (Player $record) => $record->path())
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')->label('Created')->date(),
                TextColumn::make('ttm_count')
                    ->label('TTM mail')
                    ->state(fn (Player $record) => $record->signer?->postalMails->count() ?? 0),
                TextColumn::make('address_count')
                    ->label('Addresses')
                    ->state(fn (Player $record) => $record->signer?->addresses->count() ?? 0),
                TextColumn::make('fee_count')
                    ->label('Fees')
                    ->state(fn (Player $record) => $record->signer?->fees->count() ?? 0),
                TextColumn::make('status')
                    ->state(fn (Player $record) => $record->is_currently_retired ? 'Retired' : 'Active')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Retired' ? 'warning' : 'success'),
            ])
            ->recordActions([
                Action::make('mergePlayer')
                    ->label('Keep this one')
                    ->icon(Heroicon::OutlinedArrowsPointingIn)
                    ->color('success')
                    ->authorize(fn () => auth()->user()?->can('manage', Player::class))
                    ->requiresConfirmation()
                    ->modalHeading('Merge duplicate players')
                    ->modalSubmitActionLabel('Merge')
                    ->modalDescription(function (Player $record) {
                        $siblings = app(DuplicatePlayerFinder::class)->siblingsOf($record);

                        return "\"{$record->name}\" will be kept. {$siblings->pluck('name')->implode(', ')} will be merged into it — their TTM mail, addresses, fees, tags, packs, want lists, watchlists, and set entries move to the kept record, then the duplicate(s) are deleted. This can't be undone.";
                    })
                    ->action(function (Player $record) {
                        $finder = app(DuplicatePlayerFinder::class);
                        $merger = app(PlayerMergeService::class);

                        foreach ($finder->siblingsOf($record) as $sibling) {
                            $merger->merge($record->fresh(), $sibling);
                        }

                        Notification::make()->title('Players merged')->success()->send();
                    }),
                Action::make('deletePlayer')
                    ->label('Delete')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->authorize(fn () => auth()->user()?->can('manage', Player::class))
                    ->requiresConfirmation()
                    ->modalHeading('Delete this player')
                    ->modalSubmitActionLabel('Delete')
                    ->modalDescription(function (Player $record) {
                        $mailCount = $record->signer?->postalMails->count() ?? 0;
                        $addressCount = $record->signer?->addresses->count() ?? 0;

                        $warning = "Permanently delete \"{$record->name}\"? This also removes {$mailCount} TTM mail record(s) and {$addressCount} address(es). This can't be undone.";

                        return $mailCount > 0
                            ? "{$warning} Consider merging instead if you want to keep that history."
                            : $warning;
                    })
                    ->action(function (Player $record) {
                        app(PlayerMergeService::class)->delete($record);

                        Notification::make()->title('Player deleted')->success()->send();
                    }),
            ])
            ->emptyStateHeading('No duplicate players found — nice and tidy!');
    }
}
