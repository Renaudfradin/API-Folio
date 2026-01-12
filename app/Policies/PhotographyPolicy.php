<?php

namespace App\Policies;

use App\Models\Photography;
use App\Models\User;

class PhotographyPolicy
{
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
        return true;
    }

    public function update(User $user, Photography $photography): bool
    {
        return true;
    }

    public function delete(User $user, Photography $photography): bool
    {
        return true;
    }

    public function restore(User $user, Photography $photography): bool
    {
        return true;
    }

    public function forceDelete(User $user, Photography $photography): bool
    {
        return true;
    }
}
