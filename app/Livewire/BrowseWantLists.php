<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\WantList;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class BrowseWantLists extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WantList::public()
                    ->withCount('players', 'followers')
                    ->with('user', 'category')
                    ->latest()
            )
            ->emptyStateHeading('No public lists yet')
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->url(fn (WantList $record) => $record->path()),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->url(fn (WantList $record) => route('users.profile', $record->user)),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—'),
                TextColumn::make('players_count')
                    ->label('Players')
                    ->alignCenter(),
                TextColumn::make('followers_count')
                    ->label('Followers')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id')),
            ])
            ->recordUrl(fn (WantList $record) => $record->path());
    }

    public function render()
    {
        return view('livewire.browse-want-lists')
            ->layout('layouts.app');
    }
}
