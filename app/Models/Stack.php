<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
