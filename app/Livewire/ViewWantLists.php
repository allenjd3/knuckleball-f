<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\WantList;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ViewWantLists extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WantList::query()
                    ->where('user_id', auth()->id())
                    ->withCount('players')
                    ->with('category')
                    ->latest()
            )
            ->emptyStateHeading('No want lists yet')
            ->emptyStateDescription('Create your first want list to start tracking players you want TTMs from.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create a list')
                    ->action(fn () => $this->mountAction('createList')),
            ])
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->url(fn (WantList $record) => $record->path()),
                TextColumn::make('players_count')
                    ->label('Players')
                    ->alignCenter(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—'),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->recordAction(null)
            ->recordUrl(fn (WantList $record) => $record->path())
            ->actions([
                EditAction::make()
                    ->authorize(fn (WantList $record) => auth()->user()?->can('update', $record))
                    ->schema($this->formSchema()),
                DeleteAction::make()
                    ->authorize(fn (WantList $record) => auth()->user()?->can('delete', $record)),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New List')
                    ->action(fn () => $this->mountAction('createList')),
            ]);
    }

    public function createListAction(): Action
    {
        return Action::make('createList')
            ->label('New List')
            ->authorize(fn () => auth()->user()?->can('create', WantList::class))
            ->schema($this->formSchema())
            ->action(function (array $data) {
                auth()->user()->wantLists()->create($data);
            });
    }

    private function formSchema(): array
    {
        return [
            TextInput::make('name')->required()->maxLength(255),
            Textarea::make('description')->nullable()->maxLength(1000)->rows(3),
            Select::make('category_id')
                ->label('Category')
                ->nullable()
                ->options(Category::pluck('name', 'id'))
                ->placeholder('None'),
            Toggle::make('is_public')
                ->label('Public')
                ->default(true)
                ->helperText('Public lists can be browsed and followed by other users.'),
        ];
    }

    public function render()
    {
        return view('livewire.view-want-lists')
            ->layout('layouts.app');
    }
}
