<?php

namespace App\Policies;

use App\Models\CardSet;
use App\Models\User;

class CardSetPolicy
{
    public function view(?User $user, CardSet $set): bool
    {
        if ($set->is_public) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $set->user_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, CardSet $set): bool
    {
        return $user->id === $set->user_id || $user->isSuperAdmin();
    }

    public function delete(User $user, CardSet $set): bool
    {
        return $user->id === $set->user_id || $user->isSuperAdmin();
    }
}
