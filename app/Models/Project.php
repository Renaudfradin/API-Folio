<?php

namespace App\Models;

use App\Traits\HasActiveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasActiveRouteBinding;
    use HasFactory;

    public const MORPH_TYPE = 'Project';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'url',
        'url_github',
        'stack',
        'active',
        'position',
    ];

    protected $casts = [
        'stack' => 'array',
        'position' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            if ((int) $project->position > 0) {
                return;
            }

            $project->position = (int) static::query()->max('position') + 1;
        });
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
