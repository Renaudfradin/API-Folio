<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company',
        'title',
        'date',
        'platform',
        'location',
        'link_job',
        'responce',
        'response_date',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'response_date' => 'datetime',
    ];
}
