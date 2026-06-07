<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomationTemplate extends Model
{
    protected $fillable = [
        'salon_id', 'template_key', 'name', 'channels_label', 'trigger_label',
        'is_active', 'channel_email', 'channel_sms', 'channel_whatsapp',
        'sms_body', 'email_subject', 'email_body', 'whatsapp_body',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'channel_email'     => 'boolean',
        'channel_sms'       => 'boolean',
        'channel_whatsapp'  => 'boolean',
    ];

    public function refreshChannelsLabel(): void
    {
        $this->channels_label = \App\Support\MarketingAutomationCatalog::channelsLabelFor(
            $this->template_key,
            [
                'channel_email'    => $this->channel_email,
                'channel_sms'      => $this->channel_sms,
                'channel_whatsapp' => $this->channel_whatsapp,
            ]
        );
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
