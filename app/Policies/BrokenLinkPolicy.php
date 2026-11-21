<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BrokenLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrokenLinkPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view broken links');
    }

    public function view(User $user, BrokenLink $brokenLink): bool
    {
        return $user->can('view broken links');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, BrokenLink $brokenLink): bool
    {
        return false;
    }

    public function delete(User $user, BrokenLink $brokenLink): bool
    {
        return $user->can('delete broken links');
    }

    public function restore(User $user, BrokenLink $brokenLink): bool
    {
        return $user->can('delete broken links');
    }

    public function forceDelete(User $user, BrokenLink $brokenLink): bool
    {
        return $user->can('delete broken links');
    }
}
