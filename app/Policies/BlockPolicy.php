<?php

namespace App\Policies;

use App\Models\Block;
use App\Models\User;

class BlockPolicy
{
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
        return true;
    }

    public function update(User $user, Block $block): bool
    {
        return true;
    }

    public function delete(User $user, Block $block): bool
    {
        return true;
    }

    public function restore(User $user, Block $block): bool
    {
        return true;
    }

    public function forceDelete(User $user, Block $block): bool
    {
        return true;
    }
}
