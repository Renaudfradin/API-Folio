<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photography extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'date',
        'series',
        'city',
        'camera_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
