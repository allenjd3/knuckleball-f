<?php

namespace App\Livewire;

use App\Models\Pack;
use App\Models\Player;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowPack extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Pack $pack;

    public function mount(Pack $pack): void
    {
        abort_unless($pack->is_public || auth()->user()?->can('view', $pack), 403);
    }

    #[Computed]
    public function isOwner(): bool
    {
        return auth()->id() === $this->pack->user_id || auth()->user()?->isSuperAdmin();
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return auth()->check() && $this->pack->isFollowedBy(auth()->user());
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->pack->players()->withPivot('note'))
            ->emptyStateHeading('No players yet')
            ->emptyStateDescription($this->isOwner ? 'Add players using the button above.' : 'This pack has no players yet.')
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
                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn () => $this->isOwner)
                    ->requiresConfirmation()
                    ->action(fn (Player $record) => $this->pack->players()->detach($record->id)),
            ])
            ->headerActions([
                Action::make('addPlayer')
                    ->label('Add Player')
                    ->icon('heroicon-o-plus')
                    ->visible(fn () => $this->isOwner)
                    ->form([
                        FormSelect::make('player_id')
                            ->label('Player')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => Player::where('name', 'like', "%{$search}%")
                                    ->whereNotIn('id', $this->pack->players()->select('players.id'))
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
                        $this->pack->addPlayer($player, $data['note'] ?? null);
                    }),
            ]);
    }

    public function followAction(): Action
    {
        return Action::make('follow')
            ->label($this->isFollowing ? 'Unfollow' : 'Follow')
            ->icon($this->isFollowing ? 'heroicon-o-bell-slash' : 'heroicon-o-bell')
            ->outlined()
            ->visible(fn () => auth()->check() && ! $this->isOwner && $this->pack->is_public)
            ->action(function () {
                if ($this->isFollowing) {
                    $this->pack->followers()->detach(auth()->id());
                } else {
                    $this->pack->followers()->attach(auth()->id());
                }
                unset($this->isFollowing);
            });
    }

    public function render()
    {
        return view('livewire.show-pack')
            ->layout('layouts.app');
    }
}
