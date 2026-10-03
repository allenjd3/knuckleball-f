<?php

namespace App\Policies;

use App\Models\AddressRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function update(User $user, AddressRequest $addressRequest): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isPublished();
    }

    public function delete(User $user, AddressRequest $addressRequest): bool
    {
        return $user->id === $addressRequest->user_id || $user->isSuperAdmin() || $user->isEditor();
    }
}
