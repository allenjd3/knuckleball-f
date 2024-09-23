<?php

namespace App\Policies;

use App\Models\Fee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeePolicy
{
    use HandlesAuthorization;

    public function viewAny (User $user): bool
    {

    }

    public function view (User $user, Fee $fee): bool
    {
    }

    public function create (User $user): bool
    {
    }

    public function update (User $user, Fee $fee): bool
    {
    }

    public function delete (User $user, Fee $fee): bool
    {
    }

    public function restore (User $user, Fee $fee): bool
    {
    }

    public function forceDelete (User $user, Fee $fee): bool
    {
    }
}
