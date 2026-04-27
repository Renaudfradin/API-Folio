<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use App\Traits\HasRoleBasedVisibility;

class ArticlePolicy
{
    use HasRoleBasedVisibility;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function update(User $user, Article $article): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function delete(User $user, Article $article): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function restore(User $user, Article $article): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return self::isCurrentUserAdmin();
    }
}
