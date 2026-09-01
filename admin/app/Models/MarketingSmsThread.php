<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingSmsThread extends Model
{
    protected $fillable = [
        'salon_id', 'client_id', 'display_name', 'phone', 'last_preview',
        'last_message_at', 'unread_inbound',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MarketingSmsMessage::class, 'thread_id')->orderBy('created_at', 'asc');
    }
}
