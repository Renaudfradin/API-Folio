<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
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
        'source',
        'external_id',
        'linkedin_url',
        'synced_at',
    ];

    protected $casts = [
        'stack' => 'array',
        'synced_at' => 'datetime',
    ];

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
