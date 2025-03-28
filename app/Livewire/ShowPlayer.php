<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Fee;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction as CreateTableAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
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
                TextInput::make('address_1')->required(),
                TextInput::make('address_2'),
                TextInput::make('city')->required(),
                TextInput::make('state')->required(),
                TextInput::make('postal_code')->required(),
            ])
            ->using(fn (array $data) => $this->player->address()->create($data));
    }

    public function createFeeAction(): Action
    {
        return CreateAction::make('createFee')
            ->model(Fee::class)
            ->form([
                TextInput::make('amount')->required(),
                DatePicker::make('published_at'),
                Select::make('fee_material_id')
                    ->label('Material')
                    ->relationship(name: 'feeMaterial', titleAttribute: 'name')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->createOptionModalHeading('Create Item')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
            ])
            ->using(fn (array $data) => $this->player->fees()->create($data));
    }

    public function associateTag(): Action
    {
        return Action::make('associateTag')
            ->form([
                Select::make('tag_id')
                    ->label('Tag')
                    ->options(Tag::get()
                      ->groupBy("category")
                      ->mapWithKeys(
                        fn($value, $key) => [
                          Tag::category($key) => $value->pluck("label", "id")
                        ]
                      ))
            ])
            ->action(function (array $data) {
                $this->player->addTag(data_get($data, 'tag_id'));
            });

    }

    #[Computed]
    public function fees()
    {
        return $this->player->fees()->with('feeMaterial')->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->player->postalMails()->with('feeMaterials', 'user', 'card.media'))
            ->emptyStateHeading('No TTM yet!')
            ->actions([
                TableAction::make('showCards')
                    ->label('Cards')
                    ->icon('heroicon-o-rectangle-stack')
                    ->visible(fn (Model $record) => $record->card->exists)
                    ->modalContent(fn (Model $record) => view('card-table', ['postalMail' => $record]))
                    ->slideOver()
                    ->modalSubmitActionLabel('Ok'),
                CreateTableAction::make('createCard')
                    ->modalHeading('Create Card')
                    ->label('Add Card')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn (Model $record) => auth()->user()?->can('update', $record))
                    ->form([
                        TextInput::make('manufacturer')->maxLength(255)->required(),
                        TextInput::make('series')->maxLength(255)->required(),
                        TextInput::make('year')->numeric()->required(),
                        TextInput::make('number')->nullable(),
                        TextInput::make('variation')->nullable(),
                        FileUpload::make('url')
                            ->required()
                            ->directory('cards')
                            ->image(),
                    ])
                    ->using(function (array $data, Model $record) {
                        $card = $record->cards()->create([
                            'manufacturer' => data_get($data, 'manufacturer'),
                            'user_id' => auth()->user()->id,
                            'series' => data_get($data, 'series'),
                            'year' => data_get($data, 'year'),
                            'number' => data_get($data, 'number'),
                            'variation' => data_get($data, 'variation'),
                        ]);

                        $card->media()->create(['url' => data_get($data, 'url')]);

                        return $record;
                    }),
                EditAction::make()
                    ->visible(fn (Model $record) => auth()->user()?->can('update', $record))
                    ->form([
                        DatePicker::make('date_sent'),
                        DatePicker::make('returned_date'),
                        Textarea::make('comment'),
                    ])->using(function (array $data, Model $record) {
                        $record->update($data);
                        $record->feeMaterials()->sync(data_get($data, 'fee_material_id'));

                        return $record;
                    }),
                DeleteAction::make('delete')
                    ->visible(fn (Model $record) => auth()->user()?->can('delete', $record))
                    ->requiresConfirmation(),
            ])
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('date_sent')->date(),
                TextColumn::make('returned_date')->date(),
                TextColumn::make('feeMaterials.name')->label('Item'),
                ImageColumn::make('card.media.url'),
                TextColumn::make('comment'),
            ])
            ->headerActions([
                CreateTableAction::make()
                    ->visible(fn () => auth()?->user()?->can('create', PostalMail::class))
                    ->form([
                        DatePicker::make('date_sent')->required(),
                        DatePicker::make('returned_date'),
                        Select::make('fee_material_id')
                            ->label('Material')
                            ->required()
                            ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray())
                            ->preload()
                            ->searchable()
                            ->createOptionModalHeading('Create Item')
                            ->createOptionForm([
                                TextInput::make('name'),
                            ])
                            ->createOptionUsing(fn (array $data) => FeeMaterial::create($data)->id),
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

    public function addTag(int $tagId)
    {
        $this->authorize('view', Tag::find($tagId));

        $this->player->tags()->syncWithoutDetaching([$tagId]);
    }
}
