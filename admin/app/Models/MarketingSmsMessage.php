<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingSmsMessage extends Model
{
    public $timestamps = false;

    protected $fillable = ['thread_id', 'direction', 'body', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MarketingSmsThread::class, 'thread_id');
    }
}
