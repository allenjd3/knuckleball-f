<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
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
            ->query(Player::query()->with('team'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('team.name')->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name'),
                        TextInput::make('slug'),
                        DatePicker::make('published_at'),
                    ])
                    ->visible(fn (Player $player) => auth()->user()?->can('update', $player)),
            ]);
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(Player::class)
            ->form([
                TextInput::make('name'),
                Select::make('team_id')
                    ->options(fn () => Team::get()->flatMap(fn ($team) => [$team->id => $team->name])->toArray()),
            ]);
    }

    #[Computed]
    public function players()
    {
        return Player::all();
    }
}
