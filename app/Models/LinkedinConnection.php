<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkedinConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'profile_url',
        'profile_name',
        'profile_picture_url',
        'last_synced_at',
        'raw_profile',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'last_synced_at' => 'datetime',
        'raw_profile' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
