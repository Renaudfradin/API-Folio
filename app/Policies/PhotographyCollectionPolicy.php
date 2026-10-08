<?php

namespace App\Policies;

use App\Models\PhotographyCollection;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class PhotographyCollectionPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PhotographyCollection $photographyCollection): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, PhotographyCollection $photographyCollection): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, PhotographyCollection $photographyCollection): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, PhotographyCollection $photographyCollection): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, PhotographyCollection $photographyCollection): bool
    {
        return self::isCurrentUserAdmin();
    }
}
