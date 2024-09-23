<?php

namespace App\Policies;

use App\Models\FeeMaterial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeMaterialPolicy
{
    use HandlesAuthorization;

    public function viewAny (User $user): bool
    {

    }

    public function view (User $user, FeeMaterial $feeMaterial): bool
    {
    }

    public function create (User $user): bool
    {
    }

    public function update (User $user, FeeMaterial $feeMaterial): bool
    {
    }

    public function delete (User $user, FeeMaterial $feeMaterial): bool
    {
    }

    public function restore (User $user, FeeMaterial $feeMaterial): bool
    {
    }

    public function forceDelete (User $user, FeeMaterial $feeMaterial): bool
    {
    }
}
