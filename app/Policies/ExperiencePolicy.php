<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class ExperiencePolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Experience $experience): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Experience $experience): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Experience $experience): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Experience $experience): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Experience $experience): bool
    {
        return self::isCurrentUserAdmin();
    }
}
