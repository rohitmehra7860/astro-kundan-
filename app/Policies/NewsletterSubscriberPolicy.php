<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NewsletterSubscriber;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsletterSubscriberPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view newsletter subscribers');
    }

    public function view(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->can('view newsletter subscribers');
    }

    public function create(User $user): bool
    {
        return $user->can('create newsletter subscribers');
    }

    public function update(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->can('edit newsletter subscribers');
    }

    public function delete(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->can('delete newsletter subscribers');
    }

    public function restore(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->can('delete newsletter subscribers');
    }

    public function forceDelete(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->can('delete newsletter subscribers');
    }
}
