<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class CategoryPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $Category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Category $category): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Category $category): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Category $category): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return self::isCurrentUserAdmin();
    }
}
