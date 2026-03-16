<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class DocumentPolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Document $document): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Document $document): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Document $document): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return self::isCurrentUserAdmin();
    }
}
