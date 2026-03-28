<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;
    protected $fillable = [
        'salon_id','category_id','name','slug','description','duration_minutes',
        'buffer_minutes','price','price_from','price_on_consultation','deposit_type',
        'deposit_value','online_bookable','online_booking','show_in_menu','status','sort_order','color',
    ];
    protected $casts = [
        'price'=>'decimal:2','price_from'=>'decimal:2','deposit_value'=>'decimal:2',
        'online_bookable'=>'boolean','show_in_menu'=>'boolean','price_on_consultation'=>'boolean',
    ];
    public function salon()    { return $this->belongsTo(Salon::class); }
    public function category() { return $this->belongsTo(ServiceCategory::class,'category_id'); }
    public function staff()    { return $this->belongsToMany(Staff::class,'service_staff')->withPivot('price_override')->withTimestamps(); }
    public function appointmentServices() { return $this->hasMany(AppointmentService::class); }
    protected $appends = ['is_active'];

    public function scopeActive($q) { return $q->where('status','active'); }
    public function scopeOnline($q) { return $q->where('online_bookable',true); }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'active' : 'inactive';
    }

    protected static function newFactory()
    {
        return \Database\Factories\ServiceFactory::new();
    }

}
