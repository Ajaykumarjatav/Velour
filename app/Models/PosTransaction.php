<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Traits\AuditLog;
use App\Traits\BelongsToTenant;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosTransaction extends Model
{
    use AuditLog, BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id', 'client_id', 'staff_id', 'appointment_id',
        'reference', 'subtotal', 'discount_amount', 'discount_code',
        'discount_type', 'tax_amount', 'tip_amount', 'total',
        'amount_tendered', 'change_given', 'payment_method', 'status',
        'stripe_payment_intent_id', 'notes', 'completed_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'tip_amount'       => 'decimal:2',
        'total'            => 'decimal:2',
        'amount_tendered'  => 'decimal:2',
        'change_given'     => 'decimal:2',
        'completed_at'     => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class, 'transaction_id');
    }

    /**
     * Implicit route binding runs inside the `web` stack before `InitializeTenancyFromDomain`,
     * so TenantScope often does not apply yet. Scope by the same salon the user will get as
     * tenant (session + owner/staff), otherwise we load the wrong row and policy returns 403.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        $salonId = static::inferSalonIdForImplicitBinding();

        $query = static::withoutGlobalScopes()->where($field, $value);
        if ($salonId !== null) {
            $query->where('salon_id', $salonId);
        }

        return $query->firstOrFail();
    }

    /**
     * @return int|null null when guest / unknown — binding falls back to id-only (auth runs next).
     */
    protected static function inferSalonIdForImplicitBinding(): ?int
    {
        if (Tenant::checkCurrent()) {
            return (int) Tenant::current()->getKey();
        }

        $request = request();
        if (! $request->hasSession()) {
            return null;
        }

        $user = auth()->user();
        if (! $user) {
            $guard = config('auth.defaults.guard', 'web');
            $sessionUserKey = 'login_' . $guard . '_' . sha1(SessionGuard::class);
            $userId = (int) ($request->session()->get($sessionUserKey) ?? 0);
            if ($userId <= 0) {
                return null;
            }
            $user = User::query()->find($userId);
            if (! $user) {
                return null;
            }
        }

        if ($user->salons()->exists()) {
            $activeSalonId = (int) $request->session()->get('active_salon_id', 0);
            $salon = $activeSalonId > 0
                ? $user->salons()->whereKey($activeSalonId)->first()
                : null;
            $salon ??= $user->salons()->orderBy('id')->first();

            return $salon ? (int) $salon->id : null;
        }

        $activeSalonId = (int) $request->session()->get('active_salon_id', 0);
        if ($activeSalonId > 0) {
            $onBranch = Staff::withoutGlobalScope(TenantScope::class)
                ->where('user_id', $user->id)
                ->where('salon_id', $activeSalonId)
                ->first();
            if ($onBranch) {
                return (int) $onBranch->salon_id;
            }
        }

        $staff = Staff::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->first();

        return $staff ? (int) $staff->salon_id : null;
    }

    /* ── Scopes ────────────────────────────────────────────────────────── */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('completed_at', [$from, $to]);
    }

    /** Revenue recognition: completion time, falling back to created_at when not set. */
    public function scopeRecognizedBetweenUtc($query, $fromUtc, $toUtc)
    {
        return $query->where('status', 'completed')
            ->whereRaw('COALESCE(completed_at, created_at) BETWEEN ? AND ?', [$fromUtc, $toUtc]);
    }

    /* ── Accessors ─────────────────────────────────────────────────────── */

    public function getNetTotalAttribute(): float
    {
        return $this->total - $this->tax_amount;
    }

    /* ── Boot ──────────────────────────────────────────────────────────── */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! $model->reference) {
                $model->reference = 'TXN-' . strtoupper(uniqid());
            }
        });
    }
}
