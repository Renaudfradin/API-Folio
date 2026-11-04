<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReadingStat extends Model
{
    protected $fillable = [
        'article_id',
        'locale',
        'views_count',
        'reads_count',
        'last_viewed_at',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'reads_count' => 'integer',
            'last_viewed_at' => 'datetime',
            'last_read_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
