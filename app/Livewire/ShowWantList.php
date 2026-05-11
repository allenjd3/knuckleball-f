<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\WantList;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowWantList extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public WantList $wantList;

    public function mount(WantList $wantList): void
    {
        abort_unless(auth()->user()?->can('view', $wantList) ?? $wantList->is_public, 403);
    }

    #[Computed]
    public function isOwner(): bool
    {
        return auth()->id() === $this->wantList->user_id || auth()->user()?->isSuperAdmin();
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return auth()->check() && $this->wantList->isFollowedBy(auth()->user());
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->wantList->players()->withPivot('note'))
            ->emptyStateHeading('No players yet')
            ->emptyStateDescription($this->isOwner ? 'Add players to this list using the button above.' : 'This list has no players yet.')
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->url(fn (Player $record) => $record->path()),
                TextColumn::make('pivot.note')
                    ->label('Note')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->actions([
                TableAction::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn () => $this->isOwner)
                    ->requiresConfirmation()
                    ->action(fn (Player $record) => $this->wantList->players()->detach($record->id)),
            ])
            ->headerActions([
                TableAction::make('addPlayer')
                    ->label('Add Player')
                    ->icon('heroicon-o-plus')
                    ->visible(fn () => $this->isOwner)
                    ->form([
                        Select::make('player_id')
                            ->label('Player')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => Player::where('name', 'like', "%{$search}%")
                                    ->whereNotIn('id', $this->wantList->players()->select('players.id'))
                                    ->limit(20)
                                    ->pluck('name', 'id')
                            )
                            ->getOptionLabelUsing(fn ($value) => Player::find($value)?->name),
                        Textarea::make('note')
                            ->label('Note')
                            ->placeholder('e.g. Send 3 cards, include SASE')
                            ->nullable()
                            ->maxLength(500),
                    ])
                    ->action(function (array $data) {
                        $player = Player::findOrFail($data['player_id']);
                        $this->wantList->addPlayer($player, $data['note'] ?? null);
                    }),
            ]);
    }

    public function followAction(): Action
    {
        return Action::make('follow')
            ->label($this->isFollowing ? 'Unfollow' : 'Follow')
            ->icon($this->isFollowing ? 'heroicon-o-bell-slash' : 'heroicon-o-bell')
            ->visible(fn () => auth()->check() && ! $this->isOwner && $this->wantList->is_public)
            ->action(function () {
                if ($this->isFollowing) {
                    $this->wantList->followers()->detach(auth()->id());
                } else {
                    $this->wantList->followers()->attach(auth()->id());
                }
                unset($this->isFollowing);
            });
    }

    public function render()
    {
        return view('livewire.show-want-list')
            ->layout('layouts.app');
    }
}
