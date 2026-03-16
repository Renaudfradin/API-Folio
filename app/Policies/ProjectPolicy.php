<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class ProjectPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Project $project): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return self::isCurrentUserAdmin();
    }
}
