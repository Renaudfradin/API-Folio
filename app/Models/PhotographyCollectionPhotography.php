<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PhotographyCollectionPhotography extends Pivot
{
    protected $table = 'photography_collection_photography';

    protected static function booted(): void
    {
        static::creating(function (self $pivot): void {
            if ((int) $pivot->position > 0) {
                return;
            }

            $max = static::query()
                ->where('photography_collection_id', $pivot->photography_collection_id)
                ->max('position');

            $pivot->position = ($max ?? 0) + 1;
        });
    }
}
