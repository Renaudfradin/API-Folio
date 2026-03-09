<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    use HasFactory;

    public const MORPH_TYPE = 'Camera';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'serie',
    ];

    public function photographs()
    {
        return $this->hasMany(Photography::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
