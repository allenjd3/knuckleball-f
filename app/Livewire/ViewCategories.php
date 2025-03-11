<?php

namespace App\Livewire;

use App\Models\Category;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ViewCategories extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        return view('livewire.view-categories');
    }

    public function table(Table $table)
    {
        return $table
            ->query(fn () => Category::query())
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Action::make('show_teams')
                    ->label('Teams')
                    ->url(fn (Category $record) => route('categories.teams.index', $record)),
                Action::make('show_players')
                    ->label('Players')
                    ->url(fn (Category $record) => route('categories.players.index', $record)),
            ]);
    }
}
