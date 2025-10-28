<?php

namespace App\Policies;

use App\Models\User;

class PlayerTagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPublished();
    }

    public function view(User $user): bool
    {
        return $user->isPublished();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function delete(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }
}
