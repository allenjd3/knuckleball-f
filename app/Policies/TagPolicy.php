<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->isPublished();
    }

    public function assign(User $user): bool
    {
        return $user->isPublished();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }
}
