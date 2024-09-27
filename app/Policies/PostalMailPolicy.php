<?php

namespace App\Policies;

use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostalMailPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, PostalMail $postalMail): bool {}

    public function create(User $user): bool {}

    public function update(User $user, PostalMail $postalMail): bool {}

    public function delete(User $user, PostalMail $postalMail): bool {}

    public function restore(User $user, PostalMail $postalMail): bool {}

    public function forceDelete(User $user, PostalMail $postalMail): bool {}
}
