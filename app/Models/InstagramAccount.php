<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_id',
        'page_name',
        'business_account_id',
        'username',
        'name',
        'biography',
        'website',
        'profile_picture_url',
        'access_token',
        'token_expires_at',
        'followers_count',
        'follows_count',
        'media_count',
        'latest_account_insights',
        'last_synced_at',
        'last_synced_status',
        'last_synced_error',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'latest_account_insights' => 'array',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(InstagramMedia::class);
    }

    public function syncRuns()
    {
        return $this->hasMany(InstagramSyncRun::class);
    }
}
