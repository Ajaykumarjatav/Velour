<?php
namespace App\Models;
use App\Traits\BelongsToTenant;

use App\Traits\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use AuditLog, BelongsToTenant;
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * @return list<string>
     */
    protected function extraAuditExcludeFields(): array
    {
        return ['total_spent', 'visit_count', 'last_visit_at', 'password', 'remember_token'];
    }

    protected $fillable = [
        'salon_id','loyalty_tier_id','referred_by_client_id','first_name','last_name','email','phone',
        'password','email_verified_at',
        'date_of_birth','avatar','color','tags','preferred_staff_id','allergies',
        'medical_notes','marketing_consent','sms_consent','email_consent','status',
        'is_vip','total_spent','visit_count','last_visit_at','next_appointment_at',
        'stripe_customer_id','source','gender','address','notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tags'=>'array','date_of_birth'=>'date',
        'email_verified_at'=>'datetime',
        'last_visit_at'=>'datetime','next_appointment_at'=>'datetime',
        'marketing_consent'=>'boolean','sms_consent'=>'boolean','email_consent'=>'boolean',
        'is_vip'=>'boolean','total_spent'=>'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");

        return $name !== '' ? $name : (string) ($this->phone ?? '');
    }
    public function salon()          { return $this->belongsTo(Salon::class); }
    public function loyaltyTier()    { return $this->belongsTo(LoyaltyTier::class, 'loyalty_tier_id'); }
    public function referredBy()     { return $this->belongsTo(Client::class,'referred_by_client_id'); }
    public function preferredStaff() { return $this->belongsTo(Staff::class,'preferred_staff_id'); }
    public function notes()          { return $this->hasMany(ClientNote::class)->latest(); }
    public function formulas()       { return $this->hasMany(ClientFormula::class)->latest(); }
    public function appointments()   { return $this->hasMany(Appointment::class)->latest('starts_at'); }
    public function transactions()   { return $this->hasMany(PosTransaction::class)->latest(); }
    public function reviews()        { return $this->hasMany(Review::class); }
    public function scopeVip($q)     { return $q->where('is_vip',true); }
    public function scopeLapsed($q)  { return $q->where('last_visit_at','<',now()->subDays(90)); }
    public function scopeNew($q)     { return $q->where('visit_count',0); }

    /** Visited within engagement window or has an upcoming appointment. */
    public function scopeEngagementActive($q)  { return \App\Support\ClientEngagement::scopeActive($q); }

    /** No recent visit and no upcoming appointment. */
    public function scopeEngagementInactive($q) { return \App\Support\ClientEngagement::scopeInactive($q); }

    public function isEngagementActive(): bool
    {
        return \App\Support\ClientEngagement::isActive($this);
    }

    public function engagementStatusLabel(): string
    {
        return \App\Support\ClientEngagement::label($this);
    }

    public function recalculateTotalSpent(): void
    {
        $this->total_spent = (float) $this->transactions()
            ->where('status', 'completed')
            ->sum('total');
        $this->save();
    }

    public function hasPortalAccount(): bool
    {
        return ! empty($this->password);
    }

    protected static function newFactory()
    {
        return \Database\Factories\ClientFactory::new();
    }

}
