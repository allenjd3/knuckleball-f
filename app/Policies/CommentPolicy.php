<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->isSuperAdmin();
    }

    public function restore(User $user, Comment $comment): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->isSuperAdmin();
    }
}
