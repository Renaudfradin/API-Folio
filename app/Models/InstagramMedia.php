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
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'insights' => 'array',
            'raw_data' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function account()
    {
        return $this->belongsTo(InstagramAccount::class, 'instagram_account_id');
    }
}
