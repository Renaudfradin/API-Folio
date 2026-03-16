<?php

namespace App\Policies;

use App\Models\Photography;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class PhotographyPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Photography $photography): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Photography $photography): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Photography $photography): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Photography $photography): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Photography $photography): bool
    {
        return self::isCurrentUserAdmin();
    }
}
