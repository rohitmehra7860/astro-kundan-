<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RedirectRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class RedirectRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view redirects');
    }

    public function view(User $user, RedirectRule $redirectRule): bool
    {
        return $user->can('view redirects');
    }

    public function create(User $user): bool
    {
        return $user->can('create redirects');
    }

    public function update(User $user, RedirectRule $redirectRule): bool
    {
        return $user->can('edit redirects');
    }

    public function delete(User $user, RedirectRule $redirectRule): bool
    {
        return $user->can('delete redirects');
    }

    public function restore(User $user, RedirectRule $redirectRule): bool
    {
        return $user->can('delete redirects');
    }

    public function forceDelete(User $user, RedirectRule $redirectRule): bool
    {
        return $user->can('delete redirects');
    }
}
