<?php

namespace App\Livewire;

use App\Models\Team;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewTeams extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        return view('livewire.view-teams');
    }

    public function table(Table $table)
    {
        return $table
            ->query(fn () => Team::query())
            ->columns([
            TextColumn::make('name')
                    ->url(fn (Team $record) => route('teams.show', $record))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name'),
            ]);
    }

    #[Computed]
    public function teams()
    {
        return Team::all();
    }
}
