<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
            ->query(Player::query()->with(['team', 'media']))
            ->columns([
                ImageColumn::make('media.url')->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Player $player) => $player->path()),
                TextColumn::make('team.name')->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name'),
                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn () => Team::get()->pluck('name', 'id')->toArray())
                            ->default(fn (Player $record) => $record->team_id),
                        DatePicker::make('published_at')
                            ->default(fn (Player $record) => $record->published_at),
                        FileUpload::make('url')
                            ->directory('avatars')
                            ->avatar(),
                    ])
                    ->visible(fn (Player $player) => auth()->user()?->can('update', $player))
                    ->using(function (array $data, Player $record) {
                        $data = collect($data);
                        $record->update($data->only('name', 'team_id', 'published_at')->toArray());

                        if ($url = $data->get('url')) {
                            defer(fn () => Storage::delete($record->media->url));
                            $record->media()->update(['url' => $url]);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(Player::class)
            ->form([
                TextInput::make('name'),
                Select::make('team_id')
                    ->label('Team')
                    ->options(fn () => Team::get()->pluck('name', 'id')->toArray()),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->avatar(),
            ])
            ->using(function (array $data): Model {
                $data = collect($data);
                $player = Player::create($data->only(['name', 'team_id'])->toArray());

                $player->media()->create([
                    'url' => $data->get('url'),
                ]);

                return $player;
            });
    }

    #[Computed]
    public function players()
    {
        return Player::all();
    }
}
