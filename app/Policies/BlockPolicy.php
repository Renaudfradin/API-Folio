<?php

namespace App\Policies;

use App\Models\Block;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class BlockPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Block $block): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Block $block): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Block $block): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Block $block): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Block $block): bool
    {
        return self::isCurrentUserAdmin();
    }
}
