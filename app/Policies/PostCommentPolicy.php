<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PostComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostCommentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view comments');
    }

    public function view(User $user, PostComment $comment): bool
    {
        return $user->can('view comments');
    }

    public function create(User $user): bool
    {
        return $user->can('create comments');
    }

    public function update(User $user, PostComment $comment): bool
    {
        return $user->can('edit comments');
    }

    public function delete(User $user, PostComment $comment): bool
    {
        return $user->can('delete comments');
    }

    public function restore(User $user, PostComment $comment): bool
    {
        return $user->can('delete comments');
    }

    public function forceDelete(User $user, PostComment $comment): bool
    {
        return $user->can('delete comments');
    }
}
