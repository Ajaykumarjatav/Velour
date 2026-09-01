<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * SalonSetting
 * ════════════════════════════════════════════════════════════════════════════ */
class SalonSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['salon_id', 'key', 'value', 'type'];

    public function salon(): BelongsTo { return $this->belongsTo(Salon::class); }

    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int)  $this->value,
            'float'   => (float)$this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
