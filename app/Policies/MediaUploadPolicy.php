<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MediaUpload;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaUploadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view media');
    }

    public function view(User $user, MediaUpload $mediaUpload): bool
    {
        return $user->can('view media');
    }

    public function create(User $user): bool
    {
        return $user->can('create media');
    }

    public function update(User $user, MediaUpload $mediaUpload): bool
    {
        return $user->can('edit media');
    }

    public function delete(User $user, MediaUpload $mediaUpload): bool
    {
        return $user->can('delete media');
    }

    public function restore(User $user, MediaUpload $mediaUpload): bool
    {
        return $user->can('delete media');
    }

    public function forceDelete(User $user, MediaUpload $mediaUpload): bool
    {
        return $user->can('delete media');
    }
}
