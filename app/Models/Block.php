<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'source',
        'external_id',
        'linkedin_url',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
