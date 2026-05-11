<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WantList;

class WantListPolicy
{
    public function view(?User $user, WantList $wantList): bool
    {
        if ($wantList->is_public) {
            return true;
        }

        return $user?->id === $wantList->user_id || $user?->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, WantList $wantList): bool
    {
        return $user->id === $wantList->user_id || $user->isSuperAdmin();
    }

    public function delete(User $user, WantList $wantList): bool
    {
        return $user->id === $wantList->user_id || $user->isSuperAdmin();
    }

    public function manage(User $user, WantList $wantList): bool
    {
        return $user->id === $wantList->user_id || $user->isSuperAdmin();
    }
}
