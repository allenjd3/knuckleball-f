<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ViewPlayers extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        return view('livewire.view-players');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->query())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Player $player) => $player->path()),
                TextColumn::make('retired_at_status')
                    ->label('Status')
                    ->state(fn ($record) => ! is_null($record->retired_at) && $record->retired_at?->isPast() ? 'Retired' : 'Active')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Retired' => 'warning',
                        'Active' => 'success',
                    }),
                TextColumn::make('team.name')->url(fn (Player $record) => $record?->team ? route('teams.show', $record->team) : '#')->label('Team')->sortable(),
                TextColumn::make('retired_at')
                    ->label('Retired Year')
                    ->state(fn ($record) => $record->retired_at?->format('Y') ?? ''),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name'),
                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn () => Team::get()->pluck('name', 'id')->toArray())
                            ->default(fn (Player $record) => $record->team_id),
                        Select::make('last_team_id')
                            ->label('Last Played For')
                            ->options(fn (Player $record) => Team::get()->pluck('name', 'id')->reject(fn ($team, $id) => $id === $record->team_id)->toArray())
                            ->default(fn (Player $record) => $record->last_team_id),
                        DatePicker::make('published_at')
                            ->default(fn (Player $record) => $record->published_at),
                        DatePicker::make('retired_at')
                            ->default(fn (Player $record) => $record->retired_at),
                        FileUpload::make('url')
                            ->directory('avatars')
                            ->avatar(),
                    ])
                    ->visible(fn (Player $player) => auth()->user()?->can('update', $player))
                    ->using(function (array $data, Player $record) {
                        $data = collect($data);
                        $record->update($data->toArray());

                        if ($url = $data->get('url')) {
                            $record->media()->update(['url' => $url]);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(Player::class)
            ->label(__('New Player'))
            ->form([
                TextInput::make('name'),
                Select::make('team_id')
                    ->label('Team')
                    ->relationship('team')
                    ->options(fn () => Team::get()->pluck('name', 'id')->toArray())
                    ->createOptionModalHeading('Create Team')
                    ->createOptionForm(function () {
                        return [
                            TextInput::make('name'),
                            DatePicker::make('published_at'),
                        ];
                    })
                    ->searchable()
                    ->preload(),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->nullable()
                    ->avatar(),
            ])
            ->using(function (array $data): Model {
                $data = collect($data);
                $player = Player::create($data->only(['name', 'team_id'])->toArray());

                if ($url = $data->get('url')) {
                    $player->media()->create([
                        'url' => $url,
                    ]);
                }

                return $player;
            });
    }

    public function query()
    {
        return Player::query()
            ->with(['team', 'lastTeam', 'media'])->where('published_at', '<', now()->endOfDay());
    }
}
