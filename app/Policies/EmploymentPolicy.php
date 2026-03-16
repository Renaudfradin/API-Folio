<?php

namespace App\Policies;

use App\Models\Employment;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class EmploymentPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Employment $employment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Employment $employment): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Employment $employment): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Employment $employment): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Employment $employment): bool
    {
        return self::isCurrentUserAdmin();
    }
}
