<?php

namespace App\Policies;

use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostalMailPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return true;
    }

    public function view(User $user, PostalMail $postalMail): bool {
        return $user->id === $postalMail->user_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool {
        return $user->isPublished();
    }

    public function update(User $user, PostalMail $postalMail): bool {
        return $user->id === $postalMail->user_id || $user->isSuperAdmin();
    }

    public function delete(User $user, PostalMail $postalMail): bool {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, PostalMail $postalMail): bool {}

    public function forceDelete(User $user, PostalMail $postalMail): bool {}
}
