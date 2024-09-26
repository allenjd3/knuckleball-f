<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return true;
    }

    public function view(User $user, Team $team): bool {}

    public function create(User $user): bool {
        return $user->isPublished();
    }

    public function update(User $user, Team $team): bool {
        return $user->isPublished();
    }

    public function delete(User $user, Team $team): bool {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Team $team): bool {}

    public function forceDelete(User $user, Team $team): bool {}
}
