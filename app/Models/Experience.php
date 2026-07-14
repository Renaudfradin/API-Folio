<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'company',
        'description',
        'start_date',
        'end_date',
        'type',
        'active',
        'source',
        'external_id',
        'linkedin_url',
        'synced_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
