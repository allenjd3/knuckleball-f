<?php

namespace App\Livewire;

use App\Enums\SetEntryStatus;
use App\Models\CardSet;
use App\Models\Player;
use App\Models\SetEntry;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowSet extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public CardSet $set;

    public function mount(CardSet $set): void
    {
        abort_unless($set->is_public || auth()->user()?->can('view', $set), 403);
    }

    #[Computed]
    public function isOwner(): bool
    {
        return auth()->id() === $this->set->user_id || auth()->user()?->isSuperAdmin();
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return auth()->check() && $this->set->isFollowedBy(auth()->user());
    }

    #[Computed]
    public function needItEntries()
    {
        return $this->set->entries()
            ->with('player')
            ->where('status', SetEntryStatus::NeedIt->value)
            ->orderBy('card_number')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function haveItEntries()
    {
        return $this->set->entries()
            ->with('player')
            ->where('status', SetEntryStatus::HaveItSigned->value)
            ->orderByDesc('date_signed')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function completionPct(): int
    {
        return $this->set->completionPercentage();
    }

    public function addEntryAction(): Action
    {
        return Action::make('addEntry')
            ->label('Add Card')
            ->icon('heroicon-o-plus')
            ->visible(fn () => $this->isOwner)
            ->form([
                FormSelect::make('player_id')
                    ->label('Player / Signer')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search) => Player::where('name', 'like', "%{$search}%")
                            ->limit(20)
                            ->pluck('name', 'id')
                    )
                    ->getOptionLabelUsing(fn ($value) => Player::find($value)?->name),
                TextInput::make('card_number')
                    ->label('Card Number')
                    ->placeholder('e.g. 123')
                    ->nullable()
                    ->maxLength(20),
                FormSelect::make('status')
                    ->label('Status')
                    ->options([
                        SetEntryStatus::NeedIt->value       => SetEntryStatus::NeedIt->label(),
                        SetEntryStatus::HaveItSigned->value => SetEntryStatus::HaveItSigned->label(),
                    ])
                    ->default(SetEntryStatus::NeedIt->value)
                    ->required(),
                DatePicker::make('date_signed')
                    ->label('Date Signed')
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->maxLength(500),
            ])
            ->action(function (array $data) {
                $this->set->entries()->create($data);
                unset($this->needItEntries, $this->haveItEntries, $this->completionPct);
            });
    }

    public function markSignedAction(): Action
    {
        return Action::make('markSigned')
            ->label('Mark Signed')
            ->icon('heroicon-o-check')
            ->color('success')
            ->visible(fn () => $this->isOwner)
            ->form([
                DatePicker::make('date_signed')
                    ->label('Date Signed')
                    ->default(now()->toDateString())
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->maxLength(500),
            ])
            ->action(function (array $data, array $arguments) {
                $entry = SetEntry::find($arguments['entry'] ?? null);
                if ($entry && $entry->set_id === $this->set->id) {
                    $entry->update([
                        'status'      => SetEntryStatus::HaveItSigned->value,
                        'date_signed' => $data['date_signed'],
                        'notes'       => $data['notes'],
                    ]);
                    $this->maybePushMilestoneFeed();
                }
                unset($this->needItEntries, $this->haveItEntries, $this->completionPct);
            });
    }

    public function removeEntryAction(): Action
    {
        return Action::make('removeEntry')
            ->label('Remove')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn () => $this->isOwner)
            ->action(function (array $arguments) {
                $entry = SetEntry::find($arguments['entry'] ?? null);
                if ($entry && $entry->set_id === $this->set->id) {
                    $entry->delete();
                }
                unset($this->needItEntries, $this->haveItEntries, $this->completionPct);
            });
    }

    public function followAction(): Action
    {
        return Action::make('follow')
            ->label($this->isFollowing ? 'Unfollow' : 'Follow')
            ->icon($this->isFollowing ? 'heroicon-o-bell-slash' : 'heroicon-o-bell')
            ->outlined()
            ->visible(fn () => auth()->check() && ! $this->isOwner && $this->set->is_public)
            ->action(function () {
                if ($this->isFollowing) {
                    $this->set->followers()->detach(auth()->id());
                } else {
                    $this->set->followers()->attach(auth()->id());
                }
                unset($this->isFollowing);
            });
    }

    private function maybePushMilestoneFeed(): void
    {
        $pct = $this->set->completionPercentage();

        if ($pct === 100) {
            $this->pushCompletionFeed();
            return;
        }

        if (in_array($pct, [25, 50, 75], strict: true)) {
            $this->pushMilestoneFeed($pct);
        }
    }

    private function pushCompletionFeed(): void
    {
        $this->set->loadMissing('user');
        \App\Models\Feed::create([
            'followable_id' => $this->set->user_id,
            'feedable_type' => CardSet::class,
            'feedable_id'   => $this->set->id,
            'meta'          => [
                'photo'       => $this->set->user->profile_photo_url,
                'user'        => $this->set->user->name,
                'user_path'   => $this->set->user->path(),
                'set_name'    => $this->set->name,
                'set_path'    => $this->set->path(),
                'set_year'    => $this->set->year,
                'cover_image' => $this->set->cover_image,
                'card_type'   => 'completion',
            ],
        ]);
    }

    private function pushMilestoneFeed(int $pct): void
    {
        $this->set->loadMissing('user');
        \App\Models\Feed::create([
            'followable_id' => $this->set->user_id,
            'feedable_type' => CardSet::class,
            'feedable_id'   => $this->set->id,
            'meta'          => [
                'photo'       => $this->set->user->profile_photo_url,
                'user'        => $this->set->user->name,
                'user_path'   => $this->set->user->path(),
                'set_name'    => $this->set->name,
                'set_path'    => $this->set->path(),
                'set_year'    => $this->set->year,
                'cover_image' => $this->set->cover_image,
                'percentage'  => $pct,
                'card_type'   => 'milestone',
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.show-set')
            ->layout('layouts.app');
    }
}
