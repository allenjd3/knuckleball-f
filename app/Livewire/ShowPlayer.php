<?php

namespace App\Livewire;

use App\Forms\Schema\FeeForm;
use App\Forms\Schema\PostalMailForm;
use App\Models\Address;
use App\Models\Fee;
use App\Models\Pack;
use App\Models\Player;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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
        $address = $this->player
            ->addresses()
            ->unpublished()
            ->notRejected()
            ->latest()
            ->first();

        return view(
            'livewire.show-player',
            request()->user()?->can('update', $address)
                ? ['unpublishedAddress' => $address]
                : [],
        );
    }

    #[Computed]
    public function isWatching(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->watchlist()->where('player_id', $this->player->id)->exists();
    }

    public function toggleWatchlist(): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($this->isWatching) {
            auth()->user()->watchlist()->detach($this->player->id);
        } else {
            auth()->user()->watchlist()->syncWithoutDetaching([$this->player->id]);
        }
        unset($this->isWatching);
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
            ->label(fn () => $this->address?->exists ? 'Suggest New Address' : 'Add the address!')
            // Only the empty-state "Add the address!" CTA should compete visually
            // with primary actions elsewhere on the page — once a real address is
            // already showing, suggesting an alternative is a lower-priority action.
            ->color(fn () => $this->address?->exists ? 'gray' : 'primary')
            ->outlined(fn () => (bool) $this->address?->exists)
            ->model(Address::class)
            ->authorize(fn () => request()->user()?->can('create', Address::class))
            ->schema([
                TextInput::make('address_1')->required(),
                TextInput::make('address_2'),
                TextInput::make('city')->required(),
                TextInput::make('state')->required(),
                TextInput::make('postal_code')->required(),
                DatePicker::make('expires_at')
                    ->label('Temporary — expires on')
                    ->helperText('Leave blank for a permanent address. Once this date passes, the address is archived automatically (still visible/editable in the admin panel).')
                    ->minDate(now()->addDay())
                    ->nullable(),
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
            ->icon('heroicon-o-currency-dollar')
            ->schema(FeeForm::schema())
            ->mutateDataUsing(function (array $data) {
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
            ->schema([
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

    public function addToPackAction(): Action
    {
        return Action::make('addToPack')
            ->label('Add to Pack')
            ->icon('heroicon-o-bookmark')
            ->authorize(fn () => request()->user()?->isPublished())
            ->schema([
                Select::make('pack_id')
                    ->label('Pack')
                    ->required()
                    ->options(fn () => auth()->user()->packs()->pluck('name', 'id'))
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(255),
                    ])
                    ->createOptionUsing(fn (array $data) => auth()->user()->packs()->create($data)->id),
                Textarea::make('note')
                    ->label('Note')
                    ->placeholder('e.g. Send 3 cards, include SASE')
                    ->nullable()
                    ->maxLength(500),
            ])
            ->action(function (array $data) {
                $pack = Pack::findOrFail($data['pack_id']);
                $pack->addPlayer($this->player, $data['note'] ?? null);
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
            ->emptyStateHeading('Dead quiet in this mailbox... 📪')
            ->emptyStateActions([
                Action::make('createNew')
                    ->visible(fn () => PostalMailForm::shouldBeVisibleFor(request()->user()))
                    ->label('Send the first letter')
                    ->action(fn () => $this->dispatch('openCreatePostalMail')),
                Action::make('logInToCreateNew')
                    ->visible(fn () => ! request()->user())
                    ->label('Login in to create!')
                    ->url(route('login')),
            ])
            ->recordActions([
                Action::make('showCards')
                    ->label('Cards')
                    ->icon('heroicon-o-rectangle-stack')
                    ->visible(fn (Model $record) => $record->card->exists)
                    ->modalContent(fn (Model $record) => view('card-table', ['postalMail' => $record]))
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Ok'),
                CreateAction::make('createCard')
                    ->modalHeading('Add Cards')
                    ->label('Add Card')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(function (?Model $record) {
                        if (is_null($record)) {
                            return false;
                        }

                        return request()->user()?->can('update', $record);
                    })
                    ->schema([
                        Repeater::make('cards')
                            ->label('')
                            ->schema([
                                TextInput::make('manufacturer')->maxLength(255)->required(),
                                TextInput::make('series')->maxLength(255)->required(),
                                TextInput::make('year')->numeric()->required(),
                                TextInput::make('number')->nullable(),
                                TextInput::make('variation')->nullable(),
                                FileUpload::make('url')
                                    ->label('Card Image')
                                    ->required()
                                    ->directory('cards')
                                    ->image(),
                            ])
                            ->addActionLabel('Add another card')
                            ->defaultItems(0)
                            ->minItems(1)
                            ->required(),
                    ])
                    ->using(function (array $data, Model $record) {
                        foreach (data_get($data, 'cards', []) as $cardData) {
                            $card = $record->cards()->create([
                                'manufacturer' => data_get($cardData, 'manufacturer'),
                                'user_id' => request()->user()->id,
                                'series' => data_get($cardData, 'series'),
                                'year' => data_get($cardData, 'year'),
                                'number' => data_get($cardData, 'number'),
                                'variation' => data_get($cardData, 'variation'),
                            ]);

                            $card->media()->create(['url' => data_get($cardData, 'url')]);
                        }

                        return $record;
                    }),
                EditAction::make()
                    ->visible(fn (Model $record) => request()->user()?->can('update', $record))
                    ->schema([
                        DatePicker::make('date_sent'),
                        DatePicker::make('returned_date'),
                        Toggle::make('is_failed')
                            ->label('Failed to return'),
                        Textarea::make('comment')->maxLength(255),
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
                    ->color(fn (bool $state) => $state ? 'danger' : 'success'),
            ])
            ->headerActions([
                CreateAction::make('createPostalMail')
                    ->visible(fn () => PostalMailForm::shouldBeVisibleFor(request()->user()))
                    ->schema(PostalMailForm::schema())
                    ->using(fn (array $data) => PostalMailForm::for($this->player->signer)->using($data)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    #[On('openCreatePostalMail')]
    public function openCreatePostalMail()
    {
        return $this->mountTableAction('createPostalMail');
    }
}
