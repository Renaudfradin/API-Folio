<?php

namespace App\Policies;

use App\Models\camera;
use App\Models\User;

class CameraPolicy
{
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
        return true;
    }

    public function update(User $user, camera $camera): bool
    {
        return true;
    }

    public function delete(User $user, camera $camera): bool
    {
        return true;
    }

    public function restore(User $user, camera $camera): bool
    {
        return true;
    }

    public function forceDelete(User $user, camera $camera): bool
    {
        return true;
    }
}
