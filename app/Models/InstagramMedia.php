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

    public function insightCount(string $key): int
    {
        $value = $this->insightValue($key);

        return $value ?? 0;
    }

    public function insightValue(string $key): ?int
    {
        if (! array_key_exists($key, $this->insights ?? [])) {
            return null;
        }

        $value = $this->insight($key);

        return is_numeric($value) ? (int) $value : null;
    }

    public function hasSyncedViewCount(): bool
    {
        return array_key_exists('views', $this->insights ?? []) || (int) $this->view_count > 0;
    }

    public function isStory(): bool
    {
        return strtoupper((string) $this->media_product_type) === 'STORY';
    }

    public function storyReactionsCount(): int
    {
        return $this->insightCount('reactions') ?: (int) $this->like_count;
    }

    public function storyRepliesCount(): int
    {
        return $this->insightCount('replies') ?: (int) $this->comments_count;
    }

    public function getEngagementRateAttribute(): ?float
    {
        if ($this->isStory()) {
            $reach = $this->insightCount('reach');
            $interactions = $this->storyReactionsCount() + $this->storyRepliesCount();

            if ($reach > 0) {
                return round(($interactions / $reach) * 100, 0);
            }

            $views = (int) $this->view_count;

            if ($views > 0) {
                return round(($interactions / $views) * 100, 0);
            }

            return null;
        }

        $views = (int) $this->view_count;

        if ($views > 0) {
            $interactions = (int) $this->like_count + (int) $this->comments_count;

            return round(($interactions / $views) * 100, 0);
        }

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
