<?php

namespace App\Livewire;

use App\Models\Player;
use Livewire\Component;

class CompareAutographs extends Component
{
    public Player $player;

    public function render()
    {
        $ttmPhotos = $this->player->postalMails()
            ->with('cards.media')
            ->get()
            ->flatMap(fn ($postalMail) => $postalMail->cards)
            ->flatMap(fn ($card) => $card->media)
            ->values();

        $inPersonPhotos = $this->player->inPersonAutographs()
            ->with('media')
            ->get()
            ->flatMap(fn ($autograph) => $autograph->media)
            ->values();

        return view('livewire.compare-autographs', [
            'ttmPhotos' => $ttmPhotos,
            'inPersonPhotos' => $inPersonPhotos,
        ]);
    }
}
