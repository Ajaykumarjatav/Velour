<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'salon_id',
        'provider',
        'publishable_key',
        'secret_key',
        'webhook_secret',
        'settings',
    ];

    protected $casts = [
        'settings'      => 'array',
        'secret_key'    => 'encrypted',
        'webhook_secret'=> 'encrypted',
    ];

    /**
     * Whether this gateway has enough config to process payments.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->publishable_key) && ! empty($this->secret_key);
    }

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function isStripe(): bool
    {
        return $this->provider === 'stripe';
    }
}
