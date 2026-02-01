<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

class ViewPlayersFromTeam extends ViewPlayers
{
    public string $teamName;
    public ?string $mediaUrl;

    public int $teamId;

    public function mount(
        int $team
    ) {
        $this->teamId = $team;
        $teamModel = Team::where('id', $this->teamId)->firstOrFail();
        $this->teamName = $teamModel->name;
        $this->mediaUrl = $teamModel->media?->url;
    }

    public function query()
    {
        return Player::query()
            ->where('team_id', $this->teamId)
            ->with(['team', 'lastTeam', 'media'])
            ->where('published_at', '<', now()->endOfDay());
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(Player::class)
            ->label(__('New Player'))
            ->schema([
                TextInput::make('name'),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->nullable()
                    ->avatar(),
            ])
            ->using(function (array $data): Model {
                $data = collect($data)->merge(['team_id' => $this->teamId]);
                $player = Player::create($data->only(['name', 'team_id'])->toArray());

                if ($url = $data->get('url')) {
                    $player->media()->create([
                        'url' => $url,
                    ]);
                }

                return $player;
            });
    }
}
