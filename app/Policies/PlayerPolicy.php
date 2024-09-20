<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayerPolicy
{
    use HandlesAuthorization;

    public function viewAny (User $user): bool
    {

    }

    public function view (User $user, Player $player): bool
    {
    }

    public function create (User $user): bool
    {
    }

    public function update (User $user, Player $player): bool
    {
    }

    public function delete (User $user, Player $player): bool
    {
    }

    public function restore (User $user, Player $player): bool
    {
    }

    public function forceDelete (User $user, Player $player): bool
    {
    }
}
