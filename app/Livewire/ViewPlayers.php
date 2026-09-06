<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Player;
use App\Models\Tag;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ViewPlayers extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public bool $searchPrimedFromUrl = false;

    public ?int $categoryTab = null;

    /**
     * Generic person-icon avatar shown when a player has no photo, or the
     * viewer isn't logged in and the photo is hidden.
     */
    public static function placeholderAvatarUrl(): string
    {
        return asset('images/player-avatar-placeholder.svg');
    }

    public function boot(): void
    {
        if (! $this->searchPrimedFromUrl && request()->filled('search')) {
            $this->tableSearch = (string) request()->query('search');
        }

        $this->searchPrimedFromUrl = true;
    }

    public function render()
    {
        return view('livewire.view-players');
    }

    /**
     * Whether to show the "All" + one-tab-per-category browser above the
     * table. Off on pages that are already scoped to one category or team
     * (ViewPlayersFromCategory, ViewPlayersFromTeam) since the tabs would
     * either duplicate or fight the page's own scoping.
     */
    public function shouldShowCategoryTabs(): bool
    {
        return true;
    }

    /**
     * @return Collection<int, Category>
     */
    public function categoryTabs()
    {
        return Category::query()->orderBy('name')->get(['id', 'name']);
    }

    public function setCategoryTab(?int $categoryId): void
    {
        $this->categoryTab = $categoryId;
        $this->resetPage();
        $this->flushCachedTableRecords();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->withReturnStatsSelects($this->query()))
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn (Action $action) => $action->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            )
            ->filters([
                $this->teamFilter(),
                $this->categoryFilter(),
                $this->statusFilter(),
                $this->feesRequiredFilter(),
                $this->responseRateFilter(),
                $this->tagsFilter(),
                $this->hasPhotoFilter(),
                $this->recentlyActiveFilter(),
            ])
            ->columns([
                ImageColumn::make('media.url')
                    ->label('')
                    ->circular()
                    ->size(36)
                    // Match the rest of the site: no player photos for guests.
                    ->state(fn (Player $record) => auth()->check() ? $record->media?->url : null)
                    ->defaultImageUrl(static::placeholderAvatarUrl()),
                TextColumn::make('name')
                    ->searchable()
                    // Filament's sortable(array) applies these in reverse, so
                    // last_name (listed last) ends up the primary sort key
                    // and name (listed first) only breaks ties on it.
                    ->sortable(['name', 'last_name'])
                    ->url(fn (Player $player) => $player->path()),
                TextColumn::make('retired_at_status')
                    ->label('Status')
                    ->state(function ($record) {
                        if (! is_null($record->deceased_at) && $record->deceased_at?->isPast()) {
                            return 'Deceased';
                        }

                        if ($record->is_currently_retired) {
                            return 'Retired';
                        }

                        return 'Active';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Deceased' => 'danger',
                        'Retired' => 'warning',
                        'Active' => 'success',
                    }),
                TextColumn::make('team.name')->url(fn (Player $record) => $record?->team ? route('teams.show', $record->team) : '#')->label('Team')->sortable(),
                TextColumn::make('retired_at')
                    ->label('Retired Year')
                    ->state(fn ($record) => $record->retired_at?->format('Y') ?? ($record->is_retired ? 'Yes' : '')),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn () => Team::get()->pluck('name', 'id')->toArray())
                            ->default(fn (Player $record) => $record->team_id),
                        Select::make('last_team_id')
                            ->label('Last Played For')
                            ->options(fn () => Team::get()->pluck('name', 'id')->toArray())
                            ->default(fn (Player $record) => $record->last_team_id),
                        DatePicker::make('published_at')
                            ->default(fn (Player $record) => $record->published_at),
                        Toggle::make('is_retired')
                            ->label('Retired')
                            ->helperText('Turn on if the player is retired, even if you don\'t know the year yet.')
                            ->default(fn (Player $record) => $record->is_retired),
                        DatePicker::make('retired_at')
                            ->label('Retirement Year')
                            ->default(fn (Player $record) => $record->retired_at),
                        FileUpload::make('url')
                            ->directory('avatars')
                            ->avatar(),
                    ])
                    ->visible(fn (Player $player) => auth()->user()?->can('update', $player))
                    ->using(function (array $data, Player $record) {
                        $data = collect($data)->except('url');
                        $record->update($data->toArray());

                        if ($url = $data->get('url')) {
                            $record->media()->update(['url' => $url]);
                        }
                    }),
            ])
            ->defaultSort('name');
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(Player::class)
            ->label(__('New Player'))
            ->schema([
                TextInput::make('name')->required(),
                Select::make('team_id')
                    ->label('Team')
                    ->relationship('team')
                    ->options(fn () => Team::published()
                        ->where('rejected', false)
                        ->get()
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->createOptionModalHeading('Create Team')
                    ->createOptionForm(function () {
                        return [
                            TextInput::make('name')->required(),
                        ];
                    })
                    ->createOptionUsing(function (array $data) {
                        Team::create([...$data, 'published_at' => now()->subDay()]);
                    })
                    ->searchable()
                    ->preload(),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->nullable()
                    ->avatar(),
                Checkbox::make('dmca_certification')
                    ->label('I certify that I own this image or have a legitimate license/permission to share it. I understand that Knuckleball follows a strict DMCA policy and will remove infringing content and terminate repeat infringer accounts.')
                    ->rules(fn (Get $get): array => filled($get('url')) ? ['accepted'] : [])
                    ->dehydrated(false),
            ])
            ->using(function (array $data): Model {
                $data = collect($data);
                $player = Player::create([...$data->only(['name', 'team_id']), 'published_at' => now()->subDay()]);

                if ($url = $data->get('url')) {
                    $player->media()->create([
                        'url' => $url,
                    ]);
                }

                return $player;
            });
    }

    public function query()
    {
        return Player::query()
            ->with(['team', 'lastTeam', 'media'])
            ->where(
                fn ($query) => $query->where('published_at', '<', now()->endOfDay())
                    ->where('rejected', false)
            )
            ->when(
                $this->categoryTab,
                fn (Builder $query, int $categoryId) => $query->whereHas(
                    'team',
                    fn (Builder $q) => $q->where('category_id', $categoryId)
                )
            );
    }

    protected function teamFilter(): SelectFilter
    {
        return SelectFilter::make('team_id')
            ->label('Team')
            ->relationship('team', 'name')
            ->searchable()
            ->preload();
    }

    protected function categoryFilter(): SelectFilter
    {
        return SelectFilter::make('category')
            ->options(fn () => Category::pluck('name', 'id'))
            ->query(fn (Builder $query, array $data) => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, $categoryId) => $query->whereHas('team', fn (Builder $q) => $q->where('category_id', $categoryId))
            ));
    }

    protected function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->options([
                'active' => 'Active',
                'retired' => 'Retired',
                'deceased' => 'Deceased',
            ])
            ->query(function (Builder $query, array $data) {
                return $query->when($data['value'] ?? null, function (Builder $query, string $status) {
                    return match ($status) {
                        'deceased' => $query->whereNotNull('deceased_at')->where('deceased_at', '<=', now()),
                        'retired' => $query->where(fn (Builder $q) => $q->where('is_retired', true)
                            ->orWhere(fn (Builder $q) => $q->whereNotNull('retired_at')->where('retired_at', '<=', now())))
                            ->where(fn (Builder $q) => $q->whereNull('deceased_at')->orWhere('deceased_at', '>', now())),
                        'active' => $query->where('is_retired', false)
                            ->where(fn (Builder $q) => $q->whereNull('retired_at')->orWhere('retired_at', '>', now()))
                            ->where(fn (Builder $q) => $q->whereNull('deceased_at')->orWhere('deceased_at', '>', now())),
                        default => $query,
                    };
                });
            });
    }

    protected function feesRequiredFilter(): Filter
    {
        return Filter::make('fees_required')
            ->label('Requires a fee')
            ->toggle()
            ->query(fn (Builder $query) => $query->whereHas('signer', fn (Builder $q) => $q->whereHas('fees')));
    }

    protected function responseRateFilter(): SelectFilter
    {
        return SelectFilter::make('response_rate')
            ->label('Response Rate')
            ->options([
                'high' => '80%+ (high)',
                'medium' => '40–79% (medium)',
                'low' => 'Under 40% (low)',
            ])
            ->query(fn (Builder $query, array $data) => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, string $bucket) => $this->applyResponseRateBucket($query, $bucket)
            ));
    }

    /**
     * Filament applies every filter's query() inside a nested
     * $query->where(function ($query) {...}) group so filters combine with
     * AND. That nested closure only carries WHERE conditions back to the
     * parent query — groupBy()/havingRaw() called in here get silently
     * dropped. So this compares the same ttm_resolved_count/ttm_success_count
     * definition (see withReturnStatsSelects()) as an inline correlated
     * subquery in a WHERE clause instead of referencing the SELECT aliases.
     *
     * $resolvedSql/$successSql and the bucket thresholds below are all fixed
     * values we control (not user input — see returnStatsSubquerySql()), so
     * they're interpolated directly rather than bound. Binding the thresholds
     * instead doesn't work: PDO returns float params as strings on sqlite, and
     * sqlite's storage-class comparison rules mean a TEXT value never sorts
     * >= a NUMERIC one, so `{$rateSql} >= ?` silently matches nothing in tests.
     */
    protected function applyResponseRateBucket(Builder $query, string $bucket): Builder
    {
        [$resolvedSql, $successSql] = $this->returnStatsSubquerySql();
        $hasResponses = "{$resolvedSql} > 0";
        $rate = "({$successSql} * 1.0 / {$resolvedSql})";

        return match ($bucket) {
            'high' => $query->whereRaw("{$hasResponses} AND {$rate} >= 0.8"),
            'medium' => $query->whereRaw("{$hasResponses} AND {$rate} >= 0.4 AND {$rate} < 0.8"),
            'low' => $query->whereRaw("{$hasResponses} AND {$rate} < 0.4"),
            default => $query,
        };
    }

    /**
     * wherePivotNotNull() silently fails to filter when used inside a
     * whereHas() closure (it doesn't scope to the pivot table correctly in
     * the generated EXISTS subquery) — referencing the signer_tag table
     * explicitly works correctly instead.
     */
    protected function tagsFilter(): SelectFilter
    {
        return SelectFilter::make('tags')
            ->label('Tags')
            ->multiple()
            ->options(fn () => Tag::pluck('label', 'id'))
            ->query(fn (Builder $query, array $data) => $query->when(
                filled($data['values'] ?? null),
                fn (Builder $query) => $query->whereHas('signer', fn (Builder $q) => $q->whereHas(
                    'tags',
                    fn (Builder $q2) => $q2->whereIn('tags.id', $data['values'])->whereNotNull('signer_tag.approved_at')
                ))
            ));
    }

    protected function hasPhotoFilter(): Filter
    {
        return Filter::make('has_photo')
            ->label('Has a photo')
            ->toggle()
            ->query(fn (Builder $query) => $query->whereHas('media'));
    }

    protected function recentlyActiveFilter(): Filter
    {
        return Filter::make('recently_active')
            ->label('Return in last 90 days')
            ->toggle()
            ->query(fn (Builder $query) => $query->whereHas('signer', fn (Builder $q) => $q->whereHas(
                'postalMails',
                fn (Builder $q2) => $q2->whereNotNull('returned_date')->where('returned_date', '>=', now()->subDays(90))
            )));
    }

    /**
     * Adds correlated-subquery counts for each player used by both the "Response
     * Rate" column and its filter — matching the same definition already used by
     * Signer::responseRate() (app/Models/Signer.php): pending mails (no returned_date
     * and not failed) are excluded from the rate entirely rather than counting
     * against the player. Applied here (wrapping whatever query() returns) rather
     * than inside query() itself, since ViewPlayersFromTeam/ViewPlayersFromCategory
     * override query() without calling parent — this keeps the filter working on
     * every page that shares this table() configuration.
     */
    protected function withReturnStatsSelects(Builder $query): Builder
    {
        $signableType = (new Player)->getMorphClass();

        $baseSubquery = fn () => DB::table('postal_mails')
            ->join('signers', 'signers.id', '=', 'postal_mails.signer_id')
            ->whereColumn('signers.signable_id', 'players.id')
            ->where('signers.signable_type', $signableType);

        return $query->addSelect([
            'ttm_resolved_count' => $baseSubquery()
                ->selectRaw('count(*)')
                ->where(fn ($q) => $q->whereNotNull('postal_mails.returned_date')->orWhere('postal_mails.is_failed', true)),
            'ttm_success_count' => $baseSubquery()
                ->selectRaw('count(*)')
                ->whereNotNull('postal_mails.returned_date')
                ->where('postal_mails.is_failed', false),
        ]);
    }

    /**
     * Same ttm_resolved_count/ttm_success_count definition as withReturnStatsSelects(),
     * as raw SQL snippets for use inside a WHERE clause (see the response_rate filter).
     * $signableType comes from the morph map (app/Providers/AppServiceProvider.php),
     * not user input, so it's safe to inline directly rather than bind.
     *
     * @return array{0: string, 1: string}
     */
    protected function returnStatsSubquerySql(): array
    {
        $signableType = (new Player)->getMorphClass();

        $resolved = "(select count(*) from postal_mails inner join signers on signers.id = postal_mails.signer_id where signers.signable_id = players.id and signers.signable_type = '{$signableType}' and (postal_mails.returned_date is not null or postal_mails.is_failed = 1))";
        $success = "(select count(*) from postal_mails inner join signers on signers.id = postal_mails.signer_id where signers.signable_id = players.id and signers.signable_type = '{$signableType}' and postal_mails.returned_date is not null and postal_mails.is_failed = 0)";

        return [$resolved, $success];
    }
}
