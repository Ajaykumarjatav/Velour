<?php
namespace App\Models;
use App\Traits\BelongsToTenant;

use App\Traits\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use AuditLog, BelongsToTenant;
    use HasFactory, SoftDeletes;
    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PARTIAL = 'partial';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'salon_id','client_id','staff_id','reference','starts_at','ends_at',
        'duration_minutes','total_price','deposit_paid','amount_paid','status','source','payment_status',
        'client_notes','internal_notes','reminder_sent','reminder_sent_at','reminder_dispatch_keys','review_requested',
        'confirmed_at','cancelled_at','cancellation_reason','deposit_required','deposit_paid_flag',
    ];
    protected $casts = [
        'starts_at'=>'datetime','ends_at'=>'datetime',
        'confirmed_at'=>'datetime','cancelled_at'=>'datetime',
        'total_price'=>'decimal:2','deposit_paid'=>'decimal:2','amount_paid'=>'decimal:2',
        'reminder_sent'=>'boolean','reminder_sent_at'=>'datetime','review_requested'=>'boolean',
        'reminder_dispatch_keys'=>'array',
        'deposit_required'=>'boolean','deposit_paid_flag'=>'boolean',
    ];
    protected static function boot() {
        parent::boot();
        static::creating(function ($m) {
            if (!$m->reference) $m->reference = 'APT-'.strtoupper(Str::random(8));
        });
    }
    public function salon()    { return $this->belongsTo(Salon::class); }
    public function client()   { return $this->belongsTo(Client::class); }
    public function staff()    { return $this->belongsTo(Staff::class); }
    public function services() { return $this->hasMany(AppointmentService::class); }
    public function transaction() { return $this->hasOne(PosTransaction::class); }
    public function review()   { return $this->hasOne(Review::class); }
    public function scopeUpcoming($q) { return $q->where('starts_at','>=',now())->where('status','confirmed'); }
    public function scopeToday($q)    { return $q->whereDate('starts_at',today()); }
    public function scopeCompleted($q){ return $q->where('status','completed'); }
    public function getBalanceDueAttribute(): float {
        return max(0, $this->total_price - $this->amount_paid);
    }
    protected static function newFactory()
    {
        return \Database\Factories\AppointmentFactory::new();
    }

    /** @return array<string, string> */
    public static function bookingSourceOptions(): array
    {
        return [
            'online' => 'Online',
            'phone' => 'Phone',
            'walk_in' => 'Walk-in',
            'google' => 'Google',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'whatsapp' => 'WhatsApp',
            'website_embed' => 'Website embed',
            'qr_code' => 'QR code',
            'manual' => 'Manual / desk',
            'other' => 'Other',
        ];
    }

    /** @return list<string> */
    public static function bookingSourceKeys(): array
    {
        return array_keys(self::bookingSourceOptions());
    }

    public static function sourceLabel(?string $source): string
    {
        $opts = self::bookingSourceOptions();

        return $opts[$source] ?? ucfirst(str_replace('_', ' ', (string) ($source ?? 'manual')));
    }

    /** @return array<string, string> */
    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PARTIAL => 'Partially paid',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_REFUNDED => 'Refunded',
        ];
    }

    /** @return list<string> */
    public static function paymentStatusKeys(): array
    {
        return array_keys(self::paymentStatusOptions());
    }

    public static function paymentStatusLabel(?string $status): string
    {
        $opts = self::paymentStatusOptions();

        return $opts[$status] ?? ucfirst(str_replace('_', ' ', (string) ($status ?? self::PAYMENT_UNPAID)));
    }

}
