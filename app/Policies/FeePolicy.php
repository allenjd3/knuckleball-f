<?php

namespace App\Policies;

use App\Models\Fee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Fee $fee): bool
    {
        return $user->isPublished();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, Fee $fee): bool
    {
        return $user->isPublished();
    }

    public function delete(User $user, Fee $fee): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Fee $fee): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Fee $fee): bool
    {
        return $user->isSuperAdmin();
    }
}
