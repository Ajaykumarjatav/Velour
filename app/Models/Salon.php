<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Salon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'owner_id','business_type_id','name','slug','subdomain','description','phone','email','website',
        'address_line1','address_line2','city','county','postcode','country',
        'latitude','longitude','timezone','currency','locale',
        'logo','cover_image','social_links','booking_url','google_place_id',
        'stripe_account_id','online_booking_enabled','home_services_enabled','new_client_booking_enabled',
        'deposit_required','deposit_percentage','instant_confirmation',
        'booking_advance_days','cancellation_hours','opening_hours','is_active',
    ];
    protected $casts = [
        'social_links'=>'array','opening_hours'=>'array',
        'online_booking_enabled'=>'boolean','home_services_enabled'=>'boolean','new_client_booking_enabled'=>'boolean',
        'deposit_required'=>'boolean','instant_confirmation'=>'boolean','is_active'=>'boolean',
        'deposit_percentage'=>'decimal:2','latitude'=>'decimal:7','longitude'=>'decimal:7',
    ];
    public function owner()                { return $this->belongsTo(User::class,'owner_id'); }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    /** Business verticals this location operates under (services must use one of these). */
    public function businessTypes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessType::class, 'salon_business_types')->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (Salon $salon): void {
            if ($salon->business_type_id === null) {
                throw ValidationException::withMessages([
                    'business_type_id' => ['A business type is required for every location.'],
                ]);
            }
        });

        static::created(function (Salon $salon): void {
            if ($salon->business_type_id) {
                $salon->businessTypes()->syncWithoutDetaching([(int) $salon->business_type_id]);
            }
        });
    }
    public function staff()                { return $this->hasMany(Staff::class); }
    public function serviceCategories()    { return $this->hasMany(ServiceCategory::class); }
    public function services()             { return $this->hasMany(Service::class); }

    public function servicePackages()      { return $this->hasMany(ServicePackage::class)->orderBy('sort_order'); }
    public function inventoryCategories()  { return $this->hasMany(InventoryCategory::class); }
    public function inventoryItems()       { return $this->hasMany(InventoryItem::class); }
    public function paymentGateway()       { return $this->hasOne(PaymentGateway::class); }
    public function clients()              { return $this->hasMany(Client::class); }
    public function appointments()         { return $this->hasMany(Appointment::class); }
    public function transactions()         { return $this->hasMany(PosTransaction::class); }
    public function campaigns()            { return $this->hasMany(MarketingCampaign::class); }
    public function loyaltyTiers()         { return $this->hasMany(LoyaltyTier::class)->orderBy('sort_order'); }
    public function referralSetting()      { return $this->hasOne(SalonReferralSetting::class); }
    public function marketingAutomationTemplates() { return $this->hasMany(MarketingAutomationTemplate::class)->orderBy('name'); }
    public function marketingSmsThreads()  { return $this->hasMany(MarketingSmsThread::class)->orderByDesc('last_message_at'); }
    public function reviews()              { return $this->hasMany(Review::class); }
    public function notifications()        { return $this->hasMany(SalonNotification::class); }
    public function settings()             { return $this->hasMany(SalonSetting::class); }
    public function dynamicPricingRules()  { return $this->hasMany(DynamicPricingRule::class)->orderBy('sort_order'); }
    public function salonResources()       { return $this->hasMany(SalonResource::class)->orderBy('sort_order')->orderBy('name'); }
    public function bufferRule()           { return $this->hasOne(SalonBufferRule::class); }
    public function vouchers()             { return $this->hasMany(Voucher::class); }
    public function getSetting(string $key, mixed $default=null): mixed {
        $s = $this->settings()->where('key',$key)->first();
        return $s ? $s->value : $default;
    }

    /**
     * Opening-hours row for one weekday. Keys in DB may be "monday" (settings form) or "Monday"
     * (legacy seed/API) — PHP array keys are case-sensitive, so we resolve case-insensitively.
     *
     * @param  string  $lowercaseEnglishWeekday  e.g. "monday" from Carbon::format('l') + strtolower
     * @return array<string, mixed>|null
     */
    public function openingHoursForWeekdayKey(string $lowercaseEnglishWeekday): ?array
    {
        $hours = $this->opening_hours;
        if (! is_array($hours) || $hours === []) {
            return null;
        }

        $want = strtolower($lowercaseEnglishWeekday);

        if (isset($hours[$want]) && is_array($hours[$want])) {
            return $hours[$want];
        }

        $ucfirst = ucfirst($want);
        if (isset($hours[$ucfirst]) && is_array($hours[$ucfirst])) {
            return $hours[$ucfirst];
        }

        foreach ($hours as $k => $config) {
            if (! is_string($k) || ! is_array($config)) {
                continue;
            }
            if (strtolower($k) === $want) {
                return $config;
            }
        }

        return null;
    }

    protected static function newFactory()
    {
        return \Database\Factories\SalonFactory::new();
    }

}
