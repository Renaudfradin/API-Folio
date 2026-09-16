<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_account_id',
        'media_id',
        'caption',
        'permalink',
        'media_type',
        'media_product_type',
        'media_url',
        'thumbnail_url',
        'like_count',
        'comments_count',
        'view_count',
        'timestamp',
        'insights',
        'raw_data',
        'children',
        'comments',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'insights' => 'array',
            'raw_data' => 'array',
            'children' => 'array',
            'comments' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function account()
    {
        return $this->belongsTo(InstagramAccount::class, 'instagram_account_id');
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->thumbnail_url ?? $this->media_url;
    }

    public function insight(string $key, mixed $default = null): mixed
    {
        return data_get($this->insights, $key, $default);
    }

    public function getEngagementRateAttribute(): ?float
    {
        $followers = (int) ($this->account?->followers_count ?? 0);

        if ($followers <= 0) {
            return null;
        }

        return round((($this->like_count + $this->comments_count) / $followers) * 100, 1);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllMediaItemsAttribute(): array
    {
        if (filled($this->children)) {
            return $this->children;
        }

        if (! filled($this->media_url) && ! filled($this->thumbnail_url)) {
            return [];
        }

        return [[
            'id' => $this->media_id,
            'media_type' => $this->media_type,
            'media_url' => $this->media_url,
            'thumbnail_url' => $this->thumbnail_url,
        ]];
    }
}
