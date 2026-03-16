<?php

namespace App\Traits;

trait HasRoleBasedVisibility
{
    public static function isCurrentUserAdmin(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function isCurrentUserDemo(): bool
    {
        return auth()->check() && auth()->user()->isDemo();
    }

    public static function isCurrentUserUser(): bool
    {
        return auth()->check() && auth()->user()->isUser();
    }

    public static function isCurrentUserPlatform(): bool
    {
        return auth()->check() && auth()->user()->isPlatform();
    }

    public static function visibleForAdmin(): \Closure
    {
        return fn () => self::isCurrentUserAdmin();
    }

    public static function hiddenForDemo(): \Closure
    {
        return fn () => self::isCurrentUserDemo();
    }

    public static function hiddenForUser(): \Closure
    {
        return fn () => self::isCurrentUserUser();
    }

    public static function hiddenForPlatform(): \Closure
    {
        return fn () => self::isCurrentUserPlatform();
    }

    public static function applyAdminVisibility($action)
    {
        return $action
            ->visible(self::visibleForAdmin())
            ->hidden(self::hiddenForDemo());
    }
}
