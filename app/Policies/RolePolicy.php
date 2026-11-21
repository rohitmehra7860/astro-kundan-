<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('view roles');
    }

    public function create(User $user): bool
    {
        return $user->can('create roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('edit roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('delete roles') && $role->name !== 'admin'; // Protect admin role from deletion
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->can('delete roles');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can('delete roles');
    }
}
