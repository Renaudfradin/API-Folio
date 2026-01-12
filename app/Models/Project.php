<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'url',
        'url_github',
        'stack',
    ];

    protected $casts = [
        'stack' => 'array',
    ];

    public function stack()
    {
        return $this->belongsTo(Stack::class);
    }
}
