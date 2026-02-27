<?php

namespace App\Policies;

use App\Models\Employment;
use App\Models\User;

class EmploymentPolicy
{
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
        return true;
    }

    public function update(User $user, Employment $employment): bool
    {
        return true;
    }

    public function delete(User $user, Employment $employment): bool
    {
        return true;
    }

    public function restore(User $user, Employment $employment): bool
    {
        return true;
    }

    public function forceDelete(User $user, Employment $employment): bool
    {
        return true;
    }
}
