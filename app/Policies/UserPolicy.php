<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $currentUser): bool
    {
        return $currentUser->can('view users');
    }

    public function view(User $currentUser, User $user): bool
    {
        return $currentUser->can('view users');
    }

    public function create(User $currentUser): bool
    {
        return $currentUser->can('create users');
    }

    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->can('edit users');
    }

    public function delete(User $currentUser, User $user): bool
    {
        return $currentUser->can('delete users') && $currentUser->id !== $user->id; // Can't delete self
    }

    public function restore(User $currentUser, User $user): bool
    {
        return $currentUser->can('delete users');
    }

    public function forceDelete(User $currentUser, User $user): bool
    {
        return $currentUser->can('delete users');
    }
}
