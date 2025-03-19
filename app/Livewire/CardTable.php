<?php

namespace App\Livewire;

use App\Models\Card;
use App\Models\PostalMail;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class CardTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public PostalMail $postalMail;

    public function table(Table $table): Table
    {
        return $table->query(Card::where('postal_mail_id', $this->postalMail->id))
            ->columns([
                ImageColumn::make('media.url')->size(200),
                TextColumn::make('manufacturer'),
                TextColumn::make('series'),
                TextColumn::make('year'),
                TextColumn::make('number'),
                TextColumn::make('variation'),
            ]);
    }

    public function render()
    {
        return view('livewire.card-table');
    }
}
