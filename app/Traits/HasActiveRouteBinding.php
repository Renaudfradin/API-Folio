<?php

namespace App\Traits;

trait HasActiveRouteBinding
{
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        return static::query()
            ->where($field, $value)
            ->active()
            ->firstOrFail();
    }
}
