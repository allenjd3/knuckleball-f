<?php

namespace App\Livewire;

use App\Models\Player;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewPlayers extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

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
            ]);
    }

    #[Computed]
    public function players()
    {
        return Player::all();
    }
}
