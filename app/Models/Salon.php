<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'owner_id','name','slug','subdomain','description','phone','email','website',
        'address_line1','address_line2','city','county','postcode','country',
        'latitude','longitude','timezone','currency','locale',
        'logo','cover_image','social_links','booking_url','google_place_id',
        'stripe_account_id','online_booking_enabled','new_client_booking_enabled',
        'deposit_required','deposit_percentage','instant_confirmation',
        'booking_advance_days','cancellation_hours','opening_hours','is_active',
    ];
    protected $casts = [
        'social_links'=>'array','opening_hours'=>'array',
        'online_booking_enabled'=>'boolean','new_client_booking_enabled'=>'boolean',
        'deposit_required'=>'boolean','instant_confirmation'=>'boolean','is_active'=>'boolean',
        'deposit_percentage'=>'decimal:2','latitude'=>'decimal:7','longitude'=>'decimal:7',
    ];
    public function owner()                { return $this->belongsTo(User::class,'owner_id'); }
    public function staff()                { return $this->hasMany(Staff::class); }
    public function serviceCategories()    { return $this->hasMany(ServiceCategory::class); }
    public function services()             { return $this->hasMany(Service::class); }
    public function inventoryCategories()  { return $this->hasMany(InventoryCategory::class); }
    public function inventoryItems()       { return $this->hasMany(InventoryItem::class); }
    public function paymentGateway()       { return $this->hasOne(PaymentGateway::class); }
    public function clients()              { return $this->hasMany(Client::class); }
    public function appointments()         { return $this->hasMany(Appointment::class); }
    public function transactions()         { return $this->hasMany(PosTransaction::class); }
    public function campaigns()            { return $this->hasMany(MarketingCampaign::class); }
    public function reviews()              { return $this->hasMany(Review::class); }
    public function notifications()        { return $this->hasMany(SalonNotification::class); }
    public function settings()             { return $this->hasMany(SalonSetting::class); }
    public function vouchers()             { return $this->hasMany(Voucher::class); }
    public function getSetting(string $key, mixed $default=null): mixed {
        $s = $this->settings()->where('key',$key)->first();
        return $s ? $s->value : $default;
    }
    protected static function newFactory()
    {
        return \Database\Factories\SalonFactory::new();
    }

}
