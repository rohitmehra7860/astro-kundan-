<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view services');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('view services');
    }

    public function create(User $user): bool
    {
        return $user->can('create services');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('edit services');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('delete services');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->can('delete services');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->can('delete services');
    }
}
