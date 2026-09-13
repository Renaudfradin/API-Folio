<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'documentable_id',
        'documentable_type',
    ];

    public const ALLOWED_MORPH_TYPES = [
        \App\Models\Camera::class,
        \App\Models\Project::class,
        \App\Models\Photography::class,
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
