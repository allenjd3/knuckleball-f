<?php

namespace App\Policies;

use App\Models\InPersonAutograph;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InPersonAutographPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, InPersonAutograph $inPersonAutograph): bool
    {
        return $user->id === $inPersonAutograph->user_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function update(User $user, InPersonAutograph $inPersonAutograph): bool
    {
        return $user->id === $inPersonAutograph->user_id || $user->isSuperAdmin();
    }

    public function delete(User $user, InPersonAutograph $inPersonAutograph): bool
    {
        return $user->id === $inPersonAutograph->user_id || $user->isSuperAdmin() || $user->isEditor();
    }

    public function restore(User $user, InPersonAutograph $inPersonAutograph): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, InPersonAutograph $inPersonAutograph): bool
    {
        return $user->isSuperAdmin();
    }
}
