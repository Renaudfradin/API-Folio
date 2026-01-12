<?php

namespace App\Policies;

use App\Models\Stack;
use App\Models\User;

class StackPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Stack $stack): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Stack $stack): bool
    {
        return true;
    }

    public function delete(User $user, Stack $stack): bool
    {
        return true;
    }

    public function restore(User $user, Stack $stack): bool
    {
        return true;
    }

    public function forceDelete(User $user, Stack $stack): bool
    {
        return true;
    }
}
