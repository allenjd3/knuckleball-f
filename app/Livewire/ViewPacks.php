<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Pack;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewPacks extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[Computed]
    public function packs()
    {
        return Pack::query()
            ->where('user_id', auth()->id())
            ->withCount('players')
            ->with('category')
            ->latest()
            ->get();
    }

    public function createPackAction(): Action
    {
        return Action::make('createPack')
            ->label('New Pack')
            ->icon('heroicon-o-plus')
            ->authorize(fn () => auth()->user()?->can('create', Pack::class))
            ->schema($this->formSchema())
            ->action(function (array $data) {
                auth()->user()->packs()->create($data);
                unset($this->packs);
            });
    }

    public function editPackAction(): Action
    {
        return Action::make('editPack')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments) => Pack::find($arguments['pack'] ?? null)
                ?->only(['name', 'description', 'category_id', 'is_public', 'cover_image']) ?? [])
            ->action(function (array $data, array $arguments) {
                $pack = Pack::find($arguments['pack'] ?? null);
                if ($pack && auth()->user()->can('update', $pack)) {
                    $pack->update($data);
                    unset($this->packs);
                }
            });
    }

    public function deletePackAction(): Action
    {
        return Action::make('deletePack')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (array $arguments) {
                $pack = Pack::find($arguments['pack'] ?? null);
                if ($pack && auth()->user()->can('delete', $pack)) {
                    $pack->delete();
                    unset($this->packs);
                }
            });
    }

    public function render()
    {
        return view('livewire.view-packs')
            ->layout('layouts.app');
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
                ->helperText('Public packs can be browsed and followed by other users.'),
            FileUpload::make('cover_image')
                ->label('Cover Image')
                ->image()
                ->directory('packs')
                ->visibility('public')
                ->nullable(),
        ];
    }
}
