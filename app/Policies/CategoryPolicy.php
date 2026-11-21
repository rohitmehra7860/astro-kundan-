<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'blog-categories') {
            return $user->can('view blog categories');
        }
        if ($segment === 'service-categories') {
            return $user->can('view services');
        }
        return $user->can('view categories') || $user->can('view blog categories') || $user->can('view services');
    }

    public function view(User $user, Category $category): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'service-categories') {
            return $user->can('view services');
        }
        return $user->can('view blog categories');
    }

    public function create(User $user): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'blog-categories') {
            return $user->can('create blog categories');
        }
        if ($segment === 'service-categories') {
            return $user->can('create services');
        }
        return $user->can('create categories') || $user->can('create blog categories') || $user->can('create services');
    }

    public function update(User $user, Category $category): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'service-categories') {
            return $user->can('edit services');
        }
        return $user->can('edit blog categories');
    }

    public function delete(User $user, Category $category): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'service-categories') {
            return $user->can('delete services');
        }
        return $user->can('delete blog categories');
    }

    public function restore(User $user, Category $category): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'service-categories') {
            return $user->can('delete services');
        }
        return $user->can('delete blog categories');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        $segment = request()->segment(2);
        if ($segment === 'service-categories') {
            return $user->can('delete services');
        }
        return $user->can('delete blog categories');
    }
}
