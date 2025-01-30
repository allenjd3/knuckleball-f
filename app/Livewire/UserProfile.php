<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UserProfile extends Component
{
    use WithPagination;

    public $userSlug;

    public function mount(string $user)
    {
        $this->userSlug = $user;
    }

    public function render()
    {
        return view('livewire.user-profile');
    }

    #[Computed]
    public function user()
    {
        return User::where('slug', $this->userSlug)
            ->withCount('following')
            ->withCount('followers')
            ->first();
    }

    #[Computed]
    public function postalMails()
    {
        return $this->user
            ->postalMails()
            ->orderByDesc('date_sent')
            ->with(['player', 'feeMaterials'])
            ->paginate(10);
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return auth()->user()
            ?->following()
            ->where('users.id', $this->user->id)
            ->exists();
    }

    public function follow()
    {
        auth()->user()->follow($this->user);
        unset($this->user);
        unset($this->isFollowing);
    }

    public function unfollow()
    {
        auth()->user()->unfollow($this->user);
        unset($this->user);
        unset($this->isFollowing);
    }
}
