<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Fee;
use App\Models\FeeMaterial;
use App\Models\Player;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowPlayer extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Player $player;

    public function mount(Player $player)
    {
        $this->player = $player->load('address');
    }

    public function render()
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

    public function createFeeAction(): Action
    {
        return CreateAction::make('createFee')
            ->model(Fee::class)
            ->form([
                TextInput::make('amount'),
                DatePicker::make('published_at'),
                Select::make('fee_material_id')
                    ->label('Material')
                    ->relationship(name: 'feeMaterial', titleAttribute: 'name')
                    ->createOptionForm([
                        TextInput::make('name'),
                    ]),
            ])
            ->using(fn (array $data) => $this->player->fees()->create($data));
    }

    #[Computed]
    public function fees()
    {
        return $this->player->fees()->with('feeMaterial')->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->player->postalMails()->with('feeMaterials', 'user'))
            ->emptyStateHeading('No TTM yet!')
            ->actions([
                EditAction::make()
                    ->visible(fn (Model $record) => auth()->user()->can('update', $record))
                    ->form([
                        DatePicker::make('date_sent'),
                        DatePicker::make('returned_date'),
                        Select::make('fee_material_id')
                            ->label('Material')
                            ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray()),
                        Textarea::make('comment'),
                    ])
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data) {
                            $postalMail = auth()->user()
                                ->postalMails()
                                ->create(array_merge($data, ['player_id' => $this->player->id]));

                            $postalMail->feeMaterials()->attach(data_get($data, 'fee_material_id'));

                            return $postalMail;
                        });
                    }),
                DeleteAction::make('delete')
                    ->visible(fn (Model $record) => auth()->user()->can('delete', $record))
                    ->requiresConfirmation(),
            ])
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('date_sent')->date(),
                TextColumn::make('returned_date')->date(),
                TextColumn::make('feeMaterials.name'),
                TextColumn::make('comment'),
            ])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make()
                    ->form([
                        DatePicker::make('date_sent'),
                        DatePicker::make('returned_date'),
                        Select::make('fee_material_id')
                            ->label('Material')
                            ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray()),
                        Textarea::make('comment'),
                    ])
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data) {
                            $postalMail = auth()->user()
                                ->postalMails()
                                ->create(array_merge($data, ['player_id' => $this->player->id]));

                            $postalMail->feeMaterials()->attach(data_get($data, 'fee_material_id'));

                            return $postalMail;
                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
