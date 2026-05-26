<?php

namespace App\Livewire;

use App\Models\CardSet;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewSets extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[Computed]
    public function sets()
    {
        return CardSet::query()
            ->where('user_id', auth()->id())
            ->withCount('entries')
            ->latest()
            ->get();
    }

    public function createSetAction(): Action
    {
        return Action::make('createSet')
            ->label('New Set')
            ->icon('heroicon-o-plus')
            ->authorize(fn () => auth()->user()?->can('create', CardSet::class))
            ->schema($this->formSchema())
            ->action(function (array $data) {
                auth()->user()->cardSets()->create($data);
                unset($this->sets);
            });
    }

    public function editSetAction(): Action
    {
        return Action::make('editSet')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments) => CardSet::find($arguments['set'] ?? null)
                ?->only(['name', 'year', 'manufacturer', 'total_card_count', 'description', 'is_public', 'cover_image']) ?? [])
            ->action(function (array $data, array $arguments) {
                $set = CardSet::find($arguments['set'] ?? null);
                if ($set && auth()->user()->can('update', $set)) {
                    $set->update($data);
                    unset($this->sets);
                }
            });
    }

    public function deleteSetAction(): Action
    {
        return Action::make('deleteSet')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (array $arguments) {
                $set = CardSet::find($arguments['set'] ?? null);
                if ($set && auth()->user()->can('delete', $set)) {
                    $set->delete();
                    unset($this->sets);
                }
            });
    }

    public function render()
    {
        return view('livewire.view-sets')
            ->layout('layouts.app');
    }

    private function formSchema(): array
    {
        return [
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('year')
                ->label('Year')
                ->numeric()
                ->nullable()
                ->minValue(1900)
                ->maxValue(now()->year + 1),
            TextInput::make('manufacturer')
                ->label('Manufacturer')
                ->placeholder('e.g. Topps, Upper Deck')
                ->nullable()
                ->maxLength(100),
            TextInput::make('total_card_count')
                ->label('Total Cards in Set')
                ->numeric()
                ->nullable()
                ->helperText('Total number of cards in the complete set.'),
            Textarea::make('description')->nullable()->maxLength(1000)->rows(3),
            Toggle::make('is_public')
                ->label('Public')
                ->default(true)
                ->helperText('Public sets can be browsed and followed by other users.'),
            FileUpload::make('cover_image')
                ->label('Cover Image')
                ->image()
                ->directory('sets')
                ->visibility('public')
                ->nullable(),
        ];
    }
}
