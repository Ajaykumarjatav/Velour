<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'body', 'is_admin_reply', 'is_internal',
    ];

    protected $casts = [
        'is_admin_reply' => 'boolean',
        'is_internal'    => 'boolean',
    ];

    public function ticket(): BelongsTo { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
