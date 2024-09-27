<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Player $player): bool {}

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, Player $player): bool
    {
        return $user->isPublished();
    }

    public function delete(User $user, Player $player): bool
    {
        return $user->isSuperAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Player $player): bool {}

    public function forceDelete(User $user, Player $player): bool {}
}
