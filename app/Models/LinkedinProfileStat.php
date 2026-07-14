<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkedinProfileStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'metric_key',
        'metric_label',
        'value',
        'value_text',
        'period_start',
        'period_end',
        'source',
        'payload',
        'synced_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'synced_at' => 'datetime',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
