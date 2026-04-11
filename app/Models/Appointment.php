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
    protected $fillable = [
        'salon_id','client_id','staff_id','reference','starts_at','ends_at',
        'duration_minutes','total_price','deposit_paid','amount_paid','status','source',
        'client_notes','internal_notes','reminder_sent','reminder_dispatch_keys','review_requested',
        'confirmed_at','cancelled_at','cancellation_reason','deposit_required','deposit_paid_flag',
    ];
    protected $casts = [
        'starts_at'=>'datetime','ends_at'=>'datetime',
        'confirmed_at'=>'datetime','cancelled_at'=>'datetime',
        'total_price'=>'decimal:2','deposit_paid'=>'decimal:2','amount_paid'=>'decimal:2',
        'reminder_sent'=>'boolean','review_requested'=>'boolean',
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
    public function services() { return $this->hasMany(AppointmentService::class)->with('service'); }
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

}
