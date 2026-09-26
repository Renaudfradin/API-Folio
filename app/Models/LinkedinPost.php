<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkedinPost extends Model
{
    protected $fillable = [
        'linkedin_connection_id',
        'post_urn',
        'text',
        'permalink',
        'media_type',
        'published_at',
        'impressions',
        'reactions',
        'comments',
        'shares',
        'clicks',
        'reach',
        'video_views',
        'engagement_rate',
        'raw_payload',
        'synced_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'synced_at' => 'datetime',
        'engagement_rate' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(LinkedinConnection::class, 'linkedin_connection_id');
    }
}
