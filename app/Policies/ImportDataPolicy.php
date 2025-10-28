<?php

namespace App\Policies;

use App\Models\ImportData;
use App\Models\User;

class ImportDataPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ImportData $importData): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, ImportData $importData): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, ImportData $importData): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, ImportData $importData): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, ImportData $importData): bool
    {
        return $user->isSuperAdmin();
    }
}
