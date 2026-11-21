<?php

namespace App\Modules\ContactLeads\Policies;

use App\Models\User;
use App\Modules\ContactLeads\Models\ContactLead;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactLeadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view contact leads');
    }

    public function view(User $user, ContactLead $lead): bool
    {
        return $user->can('view contact leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create contact leads');
    }

    public function update(User $user, ContactLead $lead): bool
    {
        return $user->can('edit contact leads');
    }

    public function delete(User $user, ContactLead $lead): bool
    {
        return $user->can('delete contact leads');
    }

    public function restore(User $user, ContactLead $lead): bool
    {
        return $user->can('delete contact leads');
    }

    public function forceDelete(User $user, ContactLead $lead): bool
    {
        return $user->can('delete contact leads');
    }
}
