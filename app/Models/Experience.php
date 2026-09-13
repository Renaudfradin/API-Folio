<?php

namespace App\Models;

use App\Traits\HasActiveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasActiveRouteBinding;
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
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
