<?php

namespace App\Models;

use App\Traits\HasActiveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhotographyCollection extends Model
{
    use HasActiveRouteBinding;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function photographies(): BelongsToMany
    {
        return $this->belongsToMany(
            Photography::class,
            'photography_collection_photography',
        )
            ->using(PhotographyCollectionPhotography::class)
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function activePhotographies(): BelongsToMany
    {
        return $this->photographies()->where('photographies.active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
