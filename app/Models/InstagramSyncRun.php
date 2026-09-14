<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramSyncRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_account_id',
        'status',
        'started_at',
        'finished_at',
        'records_synced',
        'error_message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function account()
    {
        return $this->belongsTo(InstagramAccount::class, 'instagram_account_id');
    }
}
