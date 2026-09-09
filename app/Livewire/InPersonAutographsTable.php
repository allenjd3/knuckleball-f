<?php

namespace App\Livewire;

use App\Forms\Schema\InPersonAutographForm;
use App\Models\Player;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Component;

class InPersonAutographsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Player $player;

    public function render()
    {
        return view('livewire.in-person-autographs-table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->player->inPersonAutographs()->with('feeMaterial', 'user', 'media'))
            ->emptyStateHeading('No in-person autographs logged yet ✍️')
            ->emptyStateActions([
                Action::make('createNew')
                    ->visible(fn () => InPersonAutographForm::shouldBeVisibleFor(request()->user()))
                    ->label('Log the first one')
                    ->action(fn () => $this->dispatch('openCreateInPersonAutograph')),
                Action::make('logInToCreateNew')
                    ->visible(fn () => ! request()->user())
                    ->label('Login in to create!')
                    ->url(route('login')),
            ])
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('obtained_date')->label('Date')->date(),
                TextColumn::make('feeMaterial.name')->label('Item'),
                ImageColumn::make('media.url')->label('Photo'),
                TextColumn::make('location'),
                TextColumn::make('is_declined')
                    ->label('Result')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Declined' : 'Obtained')
                    ->color(fn (bool $state) => $state ? 'danger' : 'success'),
                TextColumn::make('comment'),
            ])
            ->recordActions([
                Action::make('showPhotos')
                    ->label('Photos')
                    ->icon('heroicon-o-photo')
                    ->visible(fn (Model $record) => $record->media->isNotEmpty())
                    ->modalContent(fn (Model $record) => view('in-person-photos', ['media' => $record->media]))
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Ok'),
                Action::make('addPhoto')
                    ->label('Add Photo')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn (Model $record) => request()->user()?->can('update', $record))
                    ->schema([
                        FileUpload::make('photos')
                            ->label('')
                            ->multiple()
                            ->image()
                            ->directory('in-person-autographs')
                            ->required(),
                    ])
                    ->action(function (array $data, Model $record) {
                        foreach (data_get($data, 'photos', []) as $photo) {
                            $record->media()->create(['url' => $photo]);
                        }
                    }),
                EditAction::make()
                    ->visible(fn (Model $record) => request()->user()?->can('update', $record))
                    ->schema([
                        InPersonAutographForm::dateField(),
                        ...InPersonAutographForm::editableFields(),
                    ])
                    ->using(function (array $data, Model $record) {
                        $record->update($data);

                        return $record;
                    }),
                DeleteAction::make('delete')
                    ->visible(fn (Model $record) => request()->user()?->can('delete', $record))
                    ->requiresConfirmation(),
            ])
            ->headerActions([
                CreateAction::make('createInPersonAutograph')
                    ->label('Log an In Person Autograph')
                    ->visible(fn () => InPersonAutographForm::shouldBeVisibleFor(request()->user()))
                    ->schema(InPersonAutographForm::schema())
                    ->using(fn (array $data) => InPersonAutographForm::for($this->player->signer)->using($data)),
            ])
            ->defaultSort('obtained_date', 'desc');
    }

    #[On('openCreateInPersonAutograph')]
    public function openCreateInPersonAutograph()
    {
        return $this->mountTableAction('createInPersonAutograph');
    }
}
