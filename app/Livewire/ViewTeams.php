<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ViewTeams extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Category $category;

    public function render()
    {
        return view('livewire.view-teams');
    }

    public function mount(?Category $category)
    {
        $this->category = $category;
    }

    public function createTeam(): Action
    {
        return CreateAction::make('createTeam')
            ->model(Team::class)
            ->authorize(fn () => auth()->user()?->can('create', Team::class))
            ->schema([
                TextInput::make('name')->required()->maxLength(255)->minLength(1),
            ]);
    }

    public function table(Table $table)
    {
        return $table
            ->query(fn () => Team::query()
                ->where('rejected', false)
                ->published()
                ->when(
                    $this->category->exists,
                    fn ($query) => $query->where('category_id', $this->category->id),
                )
            )
            ->columns([
                TextColumn::make('name')
                    ->url(fn (Team $record) => route('teams.show', $record))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name'),
            ]);
    }
}
