<?php

namespace App\Policies;

use App\Models\camera;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class CameraPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, camera $camera): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }
}
