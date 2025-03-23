<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isPublished();
    }

    public function view(User $user, Address $address): bool
    {
        return $user->isPublished();
    }

    public function create(User $user): bool {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Address $address): bool {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Address $address): bool {
        return $user->isSuperAdmin();
    }
}
