<?php

namespace App\Policies;

use App\Models\Camera;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class CameraPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Camera $camera): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Camera $camera): bool
    {
        return self::isCurrentUserAdmin();
    }
}
