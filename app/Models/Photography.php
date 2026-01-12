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
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
