<?php

namespace App\Policies;

use App\Models\Pack;
use App\Models\User;

class PackPolicy
{
    public function view(?User $user, Pack $pack): bool
    {
        if ($pack->is_public) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $pack->user_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, Pack $pack): bool
    {
        return $user->id === $pack->user_id || $user->isSuperAdmin();
    }

    public function delete(User $user, Pack $pack): bool
    {
        return $user->id === $pack->user_id || $user->isSuperAdmin();
    }

    public function manage(User $user, Pack $pack): bool
    {
        return $user->id === $pack->user_id || $user->isSuperAdmin();
    }
}
