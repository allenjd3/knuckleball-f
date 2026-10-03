<?php

namespace App\Livewire;

use App\Enums\ActivityFilter;
use App\Enums\FailureReason;
use App\Filament\Imports\PostalMailImporter;
use App\Models\CardSet;
use App\Models\Feed;
use App\Models\Pack;
use App\Models\PostalMail;
use App\Models\User;
use App\Models\UserSnapshot;
use App\Notifications\NewFollower;
use App\Services\UserStatsService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserProfile extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithPagination;

    public string $userSlug;

    #[Url(as: 'activity', except: 'all')]
    public string $activityFilter = 'all';

    public function mount(string $user): void
    {
        $this->userSlug = $user;
    }

    public function render()
    {
        return view('livewire.user-profile');
    }

    public function updatedActivityFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function selectedActivityFilter(): ActivityFilter
    {
        return ActivityFilter::tryFrom($this->activityFilter) ?? ActivityFilter::All;
    }

    /**
     * Number of the profile owner's sends in each status filter.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function activityFilterCounts(): array
    {
        return collect(ActivityFilter::cases())
            ->reject(fn (ActivityFilter $filter) => $filter === ActivityFilter::All)
            ->mapWithKeys(fn (ActivityFilter $filter) => [
                $filter->value => $filter->apply(Feed::query()->where('followable_id', $this->user->id))->count(),
            ])
            ->all();
    }

    #[Computed]
    public function user(): User
    {
        return User::where('slug', $this->userSlug)
            ->withCount('following', 'followers')
            ->firstOrFail();
    }

    #[Computed]
    public function feeds()
    {
        return Feed::query()
            ->when(
                $this->selectedActivityFilter === ActivityFilter::All,
                fn ($q) => $q->where(fn ($q) => $q
                    ->where('followable_id', $this->user->id)
                    ->orWhereHas('mentions', fn ($q) => $q->where('user_id', $this->user->id))
                ),
                fn ($q) => $this->selectedActivityFilter->apply($q->where('followable_id', $this->user->id)),
            )
            ->orderByDesc('created_at')
            ->simplePaginate();
    }

    #[Computed]
    public function packs()
    {
        $ownProfile = auth()->id() === $this->user->id;
        $isAdmin = auth()->user()?->isSuperAdmin();

        return Pack::where('user_id', $this->user->id)
            ->when(! $ownProfile && ! $isAdmin, fn ($q) => $q->where('is_public', true))
            ->withCount('players')
            ->latest()
            ->get();
    }

    #[Computed]
    public function sets()
    {
        $ownProfile = auth()->id() === $this->user->id;
        $isAdmin = auth()->user()?->isSuperAdmin();

        return CardSet::where('user_id', $this->user->id)
            ->when(! $ownProfile && ! $isAdmin, fn ($q) => $q->where('is_public', true))
            ->withCount('entries')
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return app(UserStatsService::class)->compute($this->user);
    }

    #[Computed]
    public function snapshots()
    {
        return UserSnapshot::where('user_id', $this->user->id)
            ->orderByDesc('year')
            ->get();
    }

    #[Computed]
    public function isOwner(): bool
    {
        return auth()->id() === $this->user->id;
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return auth()->user()?->following()->where('users.id', $this->user->id)->exists() ?? false;
    }

    public function follow(): void
    {
        abort_unless(auth()->check(), 403);

        auth()->user()->follow($this->user);
        $this->user->notify(new NewFollower(auth()->user()));
        unset($this->user, $this->isFollowing);
    }

    public function unfollow(): void
    {
        abort_unless(auth()->check(), 403);

        auth()->user()->unfollow($this->user);
        unset($this->user, $this->isFollowing);
    }

    public function followersAction(): Action
    {
        return Action::make('followers')
            ->label('Followers')
            ->modalHeading('Followers')
            ->modalContent(fn () => view('user-follow-list', ['users' => $this->user->followers()->orderBy('name')->get()]))
            ->modalWidth('md')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function followingAction(): Action
    {
        return Action::make('following')
            ->label('Following')
            ->modalHeading('Following')
            ->modalContent(fn () => view('user-follow-list', ['users' => $this->user->following()->orderBy('name')->get()]))
            ->modalWidth('md')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function changeHandleAction(): Action
    {
        return Action::make('changeHandle')
            ->label('Change Handle')
            ->visible(fn () => $this->isOwner)
            ->modalHeading('Change Your Handle')
            ->modalDescription(function () {
                $changedAt = $this->user->handle_changed_at;
                if ($changedAt && $changedAt->gt(now()->subDays(30))) {
                    $available = $changedAt->addDays(30)->diffForHumans();

                    return "You can change your handle again {$available}.";
                }

                return 'Your handle is used in your profile URL and @mentions. You can change it once every 30 days.';
            })
            ->fillForm(fn () => ['handle' => $this->user->handle])
            ->schema([
                Placeholder::make('current')
                    ->label('Current handle')
                    ->content(fn () => '@' . $this->user->handle),
                TextInput::make('handle')
                    ->label('New handle')
                    ->prefix('@')
                    ->required()
                    ->minLength(3)
                    ->maxLength(30)
                    ->regex('/^[a-zA-Z0-9_]+$/')
                    ->helperText('Letters, numbers and underscores only.')
                    ->unique('users', 'handle', ignorable: $this->user),
            ])
            ->action(function (array $data) {
                $changedAt = $this->user->handle_changed_at;

                if ($changedAt && $changedAt->gt(now()->subDays(30))) {
                    $this->notify('danger', 'You can\'t change your handle yet.');

                    return;
                }

                $newHandle = $data['handle'];
                $newSlug = str($newHandle)->slug('-');

                // Ensure slug is unique (append suffix if taken by another user)
                $slug = $newSlug;
                $i = 2;
                while (User::where('slug', $slug)->where('id', '!=', $this->user->id)->exists()) {
                    $slug = $newSlug . '-' . $i++;
                }

                $this->user->update([
                    'handle' => $newHandle,
                    'slug' => $slug,
                    'handle_changed_at' => now(),
                ]);

                $this->userSlug = $slug;
                unset($this->user);

                return redirect()->route('users.profile', ['user' => $slug]);
            });
    }

    #[Computed]
    public function watchlist()
    {
        if (! $this->isOwner) {
            return collect();
        }

        return $this->user->watchlist()->with(['media'])->get();
    }

    public function removeFromWatchlist(int $playerId): void
    {
        abort_unless($this->isOwner, 403);

        auth()->user()->watchlist()->detach($playerId);
        unset($this->watchlist);
    }

    /**
     * The owner's failed sends that were logged before failure reasons existed.
     *
     * @return Collection<int, PostalMail>
     */
    #[Computed]
    public function unlabeledFailures(): Collection
    {
        if (! $this->isOwner) {
            return new Collection;
        }

        return $this->user->postalMails()
            ->where('is_failed', true)
            ->whereNull('failure_reason')
            ->with('signer.signable')
            ->latest('date_sent')
            ->limit(25)
            ->get();
    }

    public function labelFailuresAction(): Action
    {
        return Action::make('labelFailures')
            ->label('Label them')
            ->link()
            ->visible(fn () => $this->isOwner && $this->unlabeledFailures->isNotEmpty())
            ->modalHeading('What happened to these sends?')
            ->modalDescription('Pick a reason for each failed send. Leave any you are unsure about blank.')
            ->modalSubmitActionLabel('Save')
            ->schema(fn () => $this->unlabeledFailures
                ->map(fn (PostalMail $postalMail) => Select::make("reasons.{$postalMail->id}")
                    ->label(($postalMail->signer?->signable?->name ?? 'Unknown player') . ' · sent ' . ($postalMail->date_sent?->format('M j, Y') ?? '—'))
                    ->options(FailureReason::options())
                    ->placeholder('Not sure'))
                ->all())
            ->action(function (array $data) {
                $reasons = collect(data_get($data, 'reasons', []))->filter();

                $this->user->postalMails()
                    ->where('is_failed', true)
                    ->whereNull('failure_reason')
                    ->whereIn('id', $reasons->keys())
                    ->get()
                    ->each(fn (PostalMail $postalMail) => $postalMail->update([
                        'failure_reason' => $reasons->get($postalMail->id),
                    ]));

                unset($this->unlabeledFailures, $this->feeds);
            });
    }

    public function importReturnsAction(): ImportAction
    {
        return ImportAction::make('importReturns')
            ->importer(PostalMailImporter::class)
            ->label('Import Returns')
            ->visible(fn () => $this->isOwner)
            ->modalDescription(
                'Bring in your existing TTM history from a spreadsheet. Columns: player_name and date_sent '
                . '(required); returned_date, item (e.g. "Card", default), manufacturer/series/year/number/variation, '
                . 'and comment (all optional). If a player isn\'t already on Knuckleball, we\'ll create a draft for '
                . 'it and an admin will review it before it appears publicly. Multiple rows for the same player and '
                . 'date are combined into one return with multiple cards, and rows matching a return and card '
                . 'you\'ve already logged are skipped as duplicates.'
            );
    }

    public function editProfileAction(): Action
    {
        return Action::make('editProfile')
            ->label('Edit Profile')
            ->visible(fn () => $this->isOwner)
            ->fillForm(fn () => [
                'name' => $this->user->name,
                'bio' => $this->user->bio,
                'cover_photo' => $this->user->cover_photo,
                'zip_code' => $this->user->zip_code,
                'radius' => $this->user->radius ?? 50,
                'card_show_alerts' => (bool) $this->user->card_show_alerts,
            ])
            ->schema([
                TextInput::make('name')->required()->maxLength(255),
                Textarea::make('bio')->nullable()->maxLength(500)->rows(3)->label('Bio'),
                FileUpload::make('cover_photo')
                    ->label('Cover Photo')
                    ->image()
                    ->directory('covers')
                    ->visibility('public')
                    ->maxSize(10240)
                    ->helperText('Max 10MB. JPG or PNG recommended.')
                    ->nullable(),
                Section::make('Location & Alerts')
                    ->description('Used to notify you about signings and shows near you.')
                    ->schema([
                        TextInput::make('zip_code')
                            ->label('Zip / Postal Code')
                            ->nullable()
                            ->maxLength(10),
                        Select::make('radius')
                            ->label('Alert radius')
                            ->options([25 => '25 miles', 50 => '50 miles', 100 => '100 miles', 200 => '200 miles'])
                            ->default(50),
                        Toggle::make('card_show_alerts')
                            ->label('Card show alerts')
                            ->helperText('Get notified when a card show is posted near you.'),
                    ]),
            ])
            ->action(function (array $data) {
                abort_unless($this->isOwner, 403);

                $this->user->update([
                    'name' => $data['name'],
                    'bio' => $data['bio'],
                    'cover_photo' => $data['cover_photo'] ?? $this->user->cover_photo,
                    'zip_code' => $data['zip_code'] ?: null,
                    'radius' => $data['radius'] ?? 50,
                    'card_show_alerts' => $data['card_show_alerts'] ?? false,
                ]);
                unset($this->user);
            });
    }
}
