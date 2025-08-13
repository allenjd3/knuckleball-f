<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Forms\Schema\FeeForm;
use App\Models\Address;
use App\Models\Fee;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action as TableAction;
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
use Livewire\Attributes\On;
use Livewire\Component;

class ShowPlayer extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Player $player;

    public function render()
    {
        return view(
            'livewire.show-player',
            collect([])->when(request()->user()?->isSuperAdmin(),
                fn ($collection) => $collection->merge([
                    'unpublishedAddress' => $this->player
                        ->addresses()
                        ->unpublished()
                        ->notRejected()
                        ->latest()
                        ->first(),
                ]),
            )->toArray(),
        );
    }

    #[Computed]
    public function address()
    {
        return $this->player->address();
    }

    #[Computed]
    public function hasUnpublishedAddress()
    {
        return request()->user()
            ?->addresses()
            ->unpublished()
            ->notRejected()
            ->whereIn('signer_id', $this->player->signer()->select('id'))
            ->exists();
    }

    public function createAddressAction(): Action
    {
        return CreateAction::make('createAddress')
            ->model(Address::class)
            ->authorize(fn () => request()->user()?->can('create', Address::class))
            ->form([
                TextInput::make('address_1')->required(),
                TextInput::make('address_2'),
                TextInput::make('city')->required(),
                TextInput::make('state')->required(),
                TextInput::make('postal_code')->required(),
            ])
            ->using(
                function (array $data) {
                    $address = $this->player
                        ->signer
                        ->addresses()
                        ->create([
                            ...$data,
                            'user_id' => request()->user()->id,
                            'published_at' => request()->user()->isSuperAdmin() ? now() : null,
                        ]);

                    unset($this->address);

                    return $address;
                }
            );
    }

    public function createFeeAction(): Action
    {
        return CreateAction::make('createFee')
            ->model(Fee::class)
            ->authorize(fn () => request()->user()?->can('create', Fee::class))
            ->form(FeeForm::schema())
            ->mutateFormDataUsing(function (array $data) {
                data_set($data, 'user_id', request()->user()?->id);
                data_set($data, 'published_at', now()->subDay());

                return $data;
            })
            ->using(fn (array $data) => $this->player->signer->fees()->create($data));
    }

    public function editPlayer(): Action
    {
        return Action::make('editPlayer')
            ->authorize(fn () => request()->user()?->can('update', $this->player))
            ->icon('heroicon-o-pencil-square')
            ->url(route('filament.cp.resources.players.edit', $this->player));
    }

    public function associateTag(): Action
    {
        return Action::make('associateTag')
            ->icon('heroicon-o-tag')
            ->authorize(fn () => request()->user()?->can('assign', Tag::class))
            ->form([
                Select::make('tag_id')
                    ->label('Tag')
                    ->options(
                        Tag::get()
                            ->groupBy('category')
                            ->mapWithKeys(
                                fn ($value, $key) => [
                                    Tag::category($key) => $value->pluck('label', 'id'),
                                ]
                            )
                    ),
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

    #[Computed]
    public function tags()
    {
        return $this->player
            ->tags()
            ->where(fn ($query) => $query
                ->when(
                    request()->user(),
                    fn ($query) => $query->where('signer_tag.user_id', request()->user()->id)->orWhere('signer_tag.approved_at', '<', now()),
                    fn ($query) => $query->where(fn ($query) => $query->where('signer_tag.approved_at', '<', now())),
                )
            )
            ->limit(20)
            ->get();
    }

    #[On('feeUpdated')]
    public function resetFees()
    {
        unset($this->fees);
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
                    ->visible(function (?Model $record) {
                        if (is_null($record)) {
                            return false;
                        }

                        return request()->user()?->can('update', $record);
                    })
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
                            'user_id' => request()->user()->id,
                            'series' => data_get($data, 'series'),
                            'year' => data_get($data, 'year'),
                            'number' => data_get($data, 'number'),
                            'variation' => data_get($data, 'variation'),
                        ]);

                        $card->media()->create(['url' => data_get($data, 'url')]);

                        return $record;
                    }),
                EditAction::make()
                    ->visible(fn (Model $record) => request()->user()?->can('update', $record))
                    ->form([
                        DatePicker::make('date_sent'),
                        DatePicker::make('returned_date'),
                        Toggle::make('is_failed')
                            ->label("Failed to return"),
                        Textarea::make('comment'),
                    ])->using(function (array $data, Model $record) {
                        $record->update($data);

                        return $record;
                    }),
                DeleteAction::make('delete')
                    ->visible(fn (Model $record) => request()->user()?->can('delete', $record))
                    ->requiresConfirmation(),
            ])
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('date_sent')->date(),
                TextColumn::make('returned_date')->date(),
                TextColumn::make('feeMaterials.name')->label('Item'),
                ImageColumn::make('card.media.url'),
                TextColumn::make('comment'),
                TextColumn::make('is_failed')
                    ->label('Failed delivery')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Failed' : '')
                    ->color(fn (bool $state) => $state ? 'danger' : 'success')
            ])
            ->headerActions([
                CreateTableAction::make('createPostalMail')
                    ->visible(fn () => request()?->user()?->can('create', PostalMail::class))
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
                        Toggle::make('is_failed')
                            ->label("Failed to return?"),
                        Textarea::make('comment'),
                    ])
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data) {
                            $postalMail = request()->user()
                                ->postalMails()
                                ->create(array_merge($data, ['signer_id' => $this->player->signer->id]));

                            $postalMail->feeMaterials()->attach(data_get($data, 'fee_material_id'));

                            CreateFeedItem::execute(feedItem: $postalMail, comment: $postalMail->comment);

                            return $postalMail;
                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
