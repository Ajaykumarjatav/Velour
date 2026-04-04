<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomationTemplate extends Model
{
    protected $fillable = [
        'salon_id', 'template_key', 'name', 'channels_label', 'trigger_label',
        'is_active', 'sms_body', 'email_subject', 'email_body',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
