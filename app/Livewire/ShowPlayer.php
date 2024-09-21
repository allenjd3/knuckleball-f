<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Player;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class ShowPlayer extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public Player $player;

    public function mount(Player $player)
    {
        $this->player = $player->load('address');
    }
    public function render ()
    {
        return view('livewire.show-player');
    }

    public function createAddressAction(): Action
    {
        return CreateAction::make('createAddress')
            ->model(Address::class)
            ->form([
                TextInput::make('address_1'),
                TextInput::make('address_2'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('postal_code'),
            ])
            ->using(fn (array $data) => $this->player->address()->create($data));
    }
}
