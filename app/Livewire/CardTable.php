<?php

namespace App\Livewire;

use App\Models\PostalMail;
use Livewire\Component;

class CardTable extends Component
{
    public PostalMail $postalMail;

    public function render()
    {
        return view('livewire.card-table', [
            'cards' => $this->postalMail->cards()->with('media')->latest()->get(),
        ]);
    }
}
