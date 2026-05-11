<?php
namespace App\Livewire;

use App\Models\Feed;
use App\Models\Pack;
use App\Models\User;
use App\Models\UserSnapshot;
use App\Services\UserStatsService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UserProfile extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithPagination;

    public string $userSlug;

    public function mount(string $user): void
    {
        $this->userSlug = $user;
    }

    public function render()
    {
        return view('livewire.user-profile');
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
            ->where(fn ($q) => $q
                ->where('followable_id', $this->user->id)
                ->orWhereHas('mentions', fn ($q) => $q->where('user_id', $this->user->id))
            )
            ->orderByDesc('created_at')
            ->simplePaginate();
    }

    #[Computed]
    public function packs()
    {
        $ownProfile = auth()->id() === $this->user->id;
        $isAdmin    = auth()->user()?->isSuperAdmin();

        return Pack::where('user_id', $this->user->id)
            ->when(! $ownProfile && ! $isAdmin, fn ($q) => $q->where('is_public', true))
            ->withCount('players')
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
        auth()->user()->follow($this->user);
        unset($this->user, $this->isFollowing);
    }

    public function unfollow(): void
    {
        auth()->user()->unfollow($this->user);
        unset($this->user, $this->isFollowing);
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
                $newSlug   = str($newHandle)->slug('-');

                // Ensure slug is unique (append suffix if taken by another user)
                $slug = $newSlug;
                $i = 2;
                while (\App\Models\User::where('slug', $slug)->where('id', '!=', $this->user->id)->exists()) {
                    $slug = $newSlug . '-' . $i++;
                }

                $this->user->update([
                    'handle'           => $newHandle,
                    'slug'             => $slug,
                    'handle_changed_at' => now(),
                ]);

                $this->userSlug = $slug;
                unset($this->user);

                return redirect()->route('users.profile', ['user' => $slug]);
            });
    }

    public function editProfileAction(): Action
    {
        return Action::make('editProfile')
            ->label('Edit Profile')
            ->visible(fn () => $this->isOwner)
            ->fillForm(fn () => [
                'name'        => $this->user->name,
                'bio'         => $this->user->bio,
                'cover_photo' => $this->user->cover_photo,
            ])
            ->schema([
                TextInput::make('name')->required()->maxLength(255),
                Textarea::make('bio')->nullable()->maxLength(500)->rows(3)->label('Bio'),
                FileUpload::make('cover_photo')
                    ->label('Cover Photo')
                    ->image()
                    ->disk('public')
                    ->directory('covers')
                    ->maxSize(10240)
                    ->helperText('Max 10MB. JPG or PNG recommended.')
                    ->nullable(),
            ])
            ->action(function (array $data) {
                $this->user->update([
                    'name'        => $data['name'],
                    'bio'         => $data['bio'],
                    'cover_photo' => $data['cover_photo'] ?? $this->user->cover_photo,
                ]);
                unset($this->user);
            });
    }
}
